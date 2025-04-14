document.addEventListener('DOMContentLoaded', function() {
    let draggedItem = null;
    let draggedType = null;
    let initialY = 0;
    let initialRect = null;
    let isEditMode = false;
    let touchStartY = 0;

    const editButton = document.getElementById('edit-positions');
    const saveButton = document.getElementById('save-positions');
    const menuContainer = document.getElementById('menu-container');
    const addCategoryBtn = document.getElementById('add-category');
    const addProductBtn = document.getElementById('add-product');
    const categoryModal = document.getElementById('category-modal');
    const productModal = document.getElementById('product-modal');
    const categoryForm = document.getElementById('category-form');
    const productForm = document.getElementById('product-form');
    const copertoModal = document.getElementById('coperto-modal');
    const noteModal = document.getElementById('note-modal');
    const editCopertoBtn = document.getElementById('edit-coperto');
    const editNoteBtn = document.getElementById('edit-note');
    const copertoForm = document.getElementById('coperto-form');
    const noteForm = document.getElementById('note-form');
    const logoutBtn = document.getElementById('logout');

    // Gestione apertura/chiusura modali
    addCategoryBtn.addEventListener('click', () => categoryModal.style.display = 'block');
    addProductBtn.addEventListener('click', () => productModal.style.display = 'block');

    document.querySelectorAll('.modal-close').forEach(close => {
        close.addEventListener('click', () => {
            categoryModal.style.display = 'none';
            productModal.style.display = 'none';
            resetProductForm();
        });
    });

    // Chiudi modal cliccando fuori
    window.addEventListener('click', (e) => {
        if (e.target === categoryModal) {
            categoryModal.style.display = 'none';
        }
        if (e.target === productModal) {
            productModal.style.display = 'none';
            resetProductForm();
        }
    });

    // Gestione form categoria
    categoryForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('category-name').value;
        
        try {
            const response = await fetch('api/add-category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name })
            });
            
            const data = await response.json();
            if (data.success) {
                categoryModal.style.display = 'none';
                document.location.reload(true); // Forza il reload dalla server
            } else {
                throw new Error(data.error || 'Errore durante il salvataggio');
            }
        } catch (error) {
            console.error('Errore:', error);
            alert(error.message);
        }
    });

    // Gestione form prodotto
    productForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const categoryId = document.getElementById('product-category').value;
        const name = document.getElementById('product-name').value;
        const description = document.getElementById('product-description').value;
        const price = parseFloat(document.getElementById('product-price').value);
        const allergeniInputs = document.querySelectorAll('input[name="allergeni"]:checked');
        const allergeni = Array.from(allergeniInputs).map(input => parseInt(input.value));
        
        try {
            const response = await fetch('api/add-product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    categoryId,
                    name,
                    description,
                    price,
                    allergeni
                })
            });
            
            const data = await response.json();
            if (data.success) {
                productModal.style.display = 'none';
                document.location.reload(true); // Forza il reload dalla server
            } else {
                throw new Error(data.error || 'Errore durante il salvataggio');
            }
        } catch (error) {
            console.error('Errore:', error);
            alert(error.message);
        }
    });

    // Aggiungi questa funzione per resettare il form quando si chiude il modal
    function resetProductForm() {
        productForm.reset();
        document.querySelectorAll('input[name="allergeni[]"]').forEach(cb => cb.checked = false);
    }

    // Gestione dei pulsanti di controllo
    editButton.addEventListener('click', function() {
        isEditMode = true;
        menuContainer.classList.add('edit-mode');
        editButton.style.display = 'none';
        saveButton.style.display = 'inline-block';
        initializeDragAndDrop();
    });

    saveButton.addEventListener('click', async function() {
        const saved = await saveAllPositions();
        if (saved) {
            isEditMode = false;
            menuContainer.classList.remove('edit-mode');
            saveButton.style.display = 'none';
            editButton.style.display = 'inline-block';
            removeDragAndDrop();
            location.reload(); // Ricarica la pagina dopo il salvataggio
        }
    });

    function removeDragAndDrop() {
        const categories = document.querySelectorAll('.category');
        const products = document.querySelectorAll('.product');

        categories.forEach(category => {
            const handle = category.querySelector('.category-header .drag-handle');
            handle.removeEventListener('mousedown', handleDragStart);
            handle.removeEventListener('touchstart', handleDragStart);
        });

        products.forEach(product => {
            const handle = product.querySelector('.product-actions .drag-handle');
            handle.removeEventListener('mousedown', handleDragStart);
            handle.removeEventListener('touchstart', handleDragStart);
        });
    }

    function handleDragStart(e) {
        if (!isEditMode) return;
        
        e.preventDefault();
        const handle = e.target;
        const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
        
        if (handle.closest('.category-header')) {
            draggedItem = handle.closest('.category');
            draggedType = 'category';
        } else {
            draggedItem = handle.closest('.product');
            draggedType = 'product';
        }

        initialY = clientY;
        initialRect = draggedItem.getBoundingClientRect();
        
        draggedItem.classList.add('dragging');
        draggedItem.style.position = 'fixed';
        draggedItem.style.top = `${initialRect.top}px`;
        draggedItem.style.width = `${initialRect.width}px`;
        draggedItem.style.zIndex = '1000';
    }

    async function saveAllPositions() {
        const categories = document.querySelectorAll('.category');
        const categoryPositions = [];
        const productPositions = [];

        categories.forEach((category, index) => {
            const categoryId = parseInt(category.dataset.id);
            categoryPositions.push({
                id: categoryId,
                position: index + 1
            });

            const products = category.querySelectorAll('.product');
            products.forEach((product, productIndex) => {
                const productId = parseInt(product.dataset.id);
                productPositions.push({
                    id: productId,
                    position: productIndex + 1,
                    categoryId: categoryId
                });
            });
        });

        try {
            // Prima salva le posizioni delle categorie
            const categoryResponse = await fetch('api/update-positions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'category',
                    positions: categoryPositions
                })
            });

            const categoryResult = await categoryResponse.json();
            if (!categoryResult.success) {
                throw new Error(categoryResult.error || 'Errore durante il salvataggio delle categorie');
            }

            // Poi salva le posizioni dei prodotti
            const productResponse = await fetch('api/update-positions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'product',
                    positions: productPositions
                })
            });

            const productResult = await productResponse.json();
            if (!productResult.success) {
                throw new Error(productResult.error || 'Errore durante il salvataggio dei prodotti');
            }

            // Aggiorna i dataset con le nuove posizioni
            categories.forEach((category, index) => {
                category.dataset.position = index + 1;
                const products = category.querySelectorAll('.product');
                products.forEach((product, productIndex) => {
                    product.dataset.position = productIndex + 1;
                });
            });

            return true;
        } catch (error) {
            console.error('Errore durante il salvataggio:', error);
            alert('Si è verificato un errore durante il salvataggio delle posizioni: ' + error.message);
            return false;
        }
    }

    function initializeAccordion() {
        const categoryHeaders = document.querySelectorAll('.category-header');
        categoryHeaders.forEach(header => {
            header.addEventListener('click', (e) => {
                if (!e.target.closest('.category-actions')) {
                    const products = header.nextElementSibling;
                    header.classList.toggle('expanded');
                    products.classList.toggle('expanded');
                }
            });
        });
    }

    function initializeDragAndDrop() {
        const categories = document.querySelectorAll('.category');
        const products = document.querySelectorAll('.product');

        categories.forEach(category => {
            const handle = category.querySelector('.category-header .drag-handle');
            // Eventi mouse
            handle.addEventListener('mousedown', handleDragStart);
            // Eventi touch
            handle.addEventListener('touchstart', handleDragStart, { passive: false });
        });

        products.forEach(product => {
            const handle = product.querySelector('.product-actions .drag-handle');
            // Eventi mouse
            handle.addEventListener('mousedown', handleDragStart);
            // Eventi touch
            handle.addEventListener('touchstart', handleDragStart, { passive: false });
        });
    }

    function handleDragMove(e) {
        if (!draggedItem) return;

        const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
        const deltaY = clientY - initialY;
        const newY = initialRect.top + deltaY;
        
        draggedItem.style.top = `${newY}px`;

        const container = draggedType === 'category' 
            ? document.getElementById('menu-container')
            : draggedItem.closest('.products');

        const siblings = [...container.querySelectorAll(
            draggedType === 'category' 
                ? '.category:not(.dragging)' 
                : '.product:not(.dragging)'
        )];
        
        const sibling = siblings.find(sibling => {
            const box = sibling.getBoundingClientRect();
            return clientY < box.top + box.height / 2;
        });

        if (sibling) {
            container.insertBefore(draggedItem, sibling);
        } else {
            container.appendChild(draggedItem);
        }
    }

    function handleDragEnd() {
        if (!draggedItem) return;

        draggedItem.classList.remove('dragging');
        draggedItem.style.position = '';
        draggedItem.style.top = '';
        draggedItem.style.width = '';
        draggedItem.style.zIndex = '';
        
        updatePositions(draggedType);
        
        draggedItem = null;
        draggedType = null;
        initialY = 0;
        initialRect = null;
    }

    // Eventi mouse
    document.addEventListener('mousemove', handleDragMove);
    document.addEventListener('mouseup', handleDragEnd);

    // Eventi touch
    document.addEventListener('touchmove', handleDragMove, { passive: false });
    document.addEventListener('touchend', handleDragEnd);
    document.addEventListener('touchcancel', handleDragEnd);

    function updatePositions(type) {
        const items = type === 'category' 
            ? document.querySelectorAll('.category')
            : draggedItem.closest('.products').querySelectorAll('.product');

        items.forEach((item, index) => {
            item.dataset.position = index + 1;
        });
    }

    // Gestione della visibilità
    document.querySelectorAll('.toggle-visibility').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const type = this.dataset.type;
            const id = this.dataset.id;
            const item = type === 'category' 
                ? document.querySelector(`.category[data-id="${id}"]`)
                : document.querySelector(`.product[data-id="${id}"]`);

            // Aggiungiamo log per debug
            console.log('Sending request:', {
                type: type,
                id: id
            });

            fetch('api/toggle-visibility.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin', // Importante per le sessioni
                body: JSON.stringify({
                    type: type,
                    id: id
                })
            })
            .then(response => {
                // Log della risposta raw
                console.log('Raw response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    if (data.visible) {
                        item.classList.remove('hidden');
                        this.textContent = '👁️';
                    } else {
                        item.classList.add('hidden');
                        this.textContent = '👁️‍🗨️';
                    }
                } else {
                    alert('Errore: ' + (data.message || 'Errore sconosciuto'));
                }
            })
            .catch(error => {
                console.error('Detailed error:', error);
                alert('Errore durante l\'aggiornamento: ' + error.message);
            });
        });
    });

    // Inizializza solo l'accordion all'avvio
    initializeAccordion();

    // Gestione modal coperto
    editCopertoBtn.addEventListener('click', () => {
        copertoModal.style.display = 'block';
    });

    // Gestione modal note
    editNoteBtn.addEventListener('click', () => {
        noteModal.style.display = 'block';
    });

    // Chiusura modal
    document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            closeBtn.closest('.modal').style.display = 'none';
        });
    });

    // Gestione form coperto
    copertoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const price = parseFloat(document.getElementById('coperto-price').value);
        
        try {
            const response = await fetch('api/update-settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'coperto',
                    value: price
                })
            });
            
            const data = await response.json();
            if (data.success) {
                copertoModal.style.display = 'none';
                location.reload(); // Ricarica la pagina dopo il salvataggio
            }
        } catch (error) {
            console.error('Errore:', error);
        }
    });

    // Gestione form note
    noteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const note = document.getElementById('menu-note').value;
        
        try {
            const response = await fetch('api/update-settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'note',
                    value: note
                })
            });
            
            const data = await response.json();
            if (data.success) {
                noteModal.style.display = 'none';
                location.reload(); // Ricarica la pagina dopo il salvataggio
            }
        } catch (error) {
            console.error('Errore:', error);
        }
    });

    logoutBtn.addEventListener('click', async function() {
        try {
            const response = await fetch('api/logout.php');
            const data = await response.json();
            
            if (data.success) {
                window.location.href = 'login.php';
            }
        } catch (error) {
            console.error('Errore durante il logout:', error);
            alert('Errore durante il logout');
        }
    });
});

function showAllergeniModal() {
    document.getElementById('allergeni-modal').style.display = 'block';
}

function closeAllergeniModal() {
    document.getElementById('allergeni-modal').style.display = 'none';
}

// Chiudi il modal se l'utente clicca fuori dall'immagine
window.onclick = function(event) {
    const modal = document.getElementById('allergeni-modal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}



