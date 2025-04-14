document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const itemIdElement = document.getElementById('item-id');
        const itemTypeElement = document.getElementById('item-type');
        
        if (!itemIdElement || !itemTypeElement) {
            console.error('Elementi base del form mancanti');
            alert('Errore: elementi del form mancanti');
            return;
        }

        const itemId = itemIdElement.value;
        const itemType = itemTypeElement.value;
        
        let data = {
            id: parseInt(itemId)
        };

        if (itemType === 'category') {
            const nameElement = document.getElementById('category-name');
            if (!nameElement) {
                console.error('Elemento nome categoria mancante');
                alert('Errore: elemento nome categoria mancante');
                return;
            }
            data.name = nameElement.value.trim();
        } else if (itemType === 'product') {
            const nameElement = document.getElementById('product-name');
            const descriptionElement = document.getElementById('product-description');
            const priceElement = document.getElementById('product-price');
            const categoryElement = document.getElementById('product-category');
            const positionElement = document.getElementById('item-position');

            if (!nameElement || !descriptionElement || !priceElement || !categoryElement) {
                console.error('Elementi del prodotto mancanti');
                alert('Errore: elementi del prodotto mancanti');
                return;
            }

            data = {
                id: itemId,
                name: nameElement.value.trim(),
                description: descriptionElement.value.trim(),
                price: parseFloat(priceElement.value),
                categoryId: parseInt(categoryElement.value),
                position: parseInt(positionElement.value),
                allergeni: Array.from(document.querySelectorAll('input[name="allergeni[]"]:checked'))
                    .map(cb => parseInt(cb.value))
            };
        }

        try {
            const response = await fetch(`api/update-${itemType}.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Risposta server:', result);
            
            if (result.success) {
                window.location.href = 'index.php';
            } else {
                throw new Error(result.error || 'Errore durante il salvataggio');
            }
        } catch (error) {
            console.error('Errore:', error);
            alert('Errore durante il salvataggio: ' + error.message);
        }
    });
});

// Funzione per eliminare un elemento
async function deleteItem(type, id) {
    if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
        return;
    }
    
    try {
        const response = await fetch(`api/delete-${type}.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        if (result.success) {
            window.location.href = 'index.php';
        } else {
            throw new Error(result.error || 'Errore durante l\'eliminazione');
        }
    } catch (error) {
        console.error('Errore:', error);
        alert('Errore durante l\'eliminazione: ' + error.message);
    }
}





