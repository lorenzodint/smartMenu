<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$menu = getMenu($_SESSION['ristoranteID']);
?>


<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Design</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?php if (isset($menu['tema'])): ?>
        <style>
            :root {
                --primary-color: <?= htmlspecialchars($menu['tema']['primary-color']) ?>;
                --accent-color: <?= htmlspecialchars($menu['tema']['accent-color']) ?>;
                --text-color: <?= htmlspecialchars($menu['tema']['text-color']) ?>;
                --background-color: <?= htmlspecialchars($menu['tema']['background-color']) ?>;
                --card-background: <?= htmlspecialchars($menu['tema']['card-background']) ?>;
            }
        </style>
    <?php endif; ?>

    <link rel="stylesheet" href="assets/css/menu-design.css">
    <!-- <link rel="stylesheet" href="assets/css/animazione.css"> -->

    <!-- Modal per gli allergeni -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 1rem;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            height: auto;
        }

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .modal-close:hover,
        .modal-close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .test-allergeni {
            /* background-color: red; */
            height: auto;
            margin: 0 auto;
            width: 20rem;
            /* background-image: url("assets//img//allergeni.png");
            object-fit: cover; */
        }

        .test-allergeni img {
            object-fit: fill;
        }
    </style>

</head>

<body>
    <?php if ($menu): ?>

        <div class="hero">
            <img src="assets/img/logoCalaLuna.png" alt="Logo Cala Luna">
        </div>
        <div class="container">
            <h1>Menu</h1>

            <?php if ($menu): ?>
                <?php
                usort($menu['categories'], function ($a, $b) {
                    return $a['position'] - $b['position'];
                });

                foreach ($menu['categories'] as $category):
                    if (!$category['visible']) continue;
                ?>
                    <div class="category">
                        <div class="category-header">
                            <h2><?= htmlspecialchars($category['name']) ?></h2>
                            <!-- <div>pulsante</div> -->
                        </div>

                        <div class="products categoria-animazione">
                            <?php
                            usort($category['products'], function ($a, $b) {
                                return $a['position'] - $b['position'];
                            });

                            foreach ($category['products'] as $product):
                                if (!$product['visible']) continue;
                            ?>
                                <div class="product prodotto-animazione">
                                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    <p class="description"><?= htmlspecialchars($product['description']) ?></p>
                                    <div class="cont-price-allergeni">
                                        <p class="allergeni" onclick="showAllergeniModal()">
                                            <?php
                                            if (isset($product['allergeni']) && is_array($product['allergeni']) && !empty($product['allergeni'])) {
                                                echo 'Allergeni: ' . implode(', ', array_map('htmlspecialchars', $product['allergeni']));
                                            } else {
                                                echo 'Nessun allergene';
                                            }
                                            ?>
                                        </p>
                                        <p class="price"><?= number_format($product['price'], 2) ?></p>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Menu non disponibile</p>
            <?php endif; ?>

            <div class="coperto">
                <p><span>Coperto: </span>€<?= number_format($menu['coperto'], 2) ?></p>

            </div>

            <div class="testo-aggiuntivo">
                <?php if (!empty($menu['note'])): ?>
                    <p><span>Note: </span><?= htmlspecialchars($menu['note']) ?></p>
                <?php endif; ?>
            </div>


            <div class="social">
                <?php if (isset($menu['social']) && !empty($menu['social'])): ?>
                    <?php foreach ($menu['social'] as $social): ?>
                        <a href="<?= htmlspecialchars($social['link']) ?>" target="_blank" rel="noopener noreferrer">
                            <!-- <img class="social-icon-color" src="<?= htmlspecialchars($social['icona']) ?>" alt="Social media icon"> -->
                            <?= htmlspecialchars($social['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal per gli allergeni -->
        <div id="allergeni-modal" class="modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeAllergeniModal()">&times;</span>
                <!-- <img src="assets/img/allergeni.png" alt="Tabella allergeni" style="width: 100%; height: auto;"> -->
                <div class="test-allergeni">
                    <img src="assets/img/allergeni.png" alt="Tabella allergeni" style="width: 100%; height: auto;">
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>Menu non disponibile</p>
    <?php endif; ?>

    <script>
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

        document.addEventListener('DOMContentLoaded', function() {
            const categoryHeaders = document.querySelectorAll('.category-header');

            categoryHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const products = header.nextElementSibling;
                    const isClosing = header.classList.contains('active');

                    // Chiudi tutti gli altri
                    categoryHeaders.forEach(otherHeader => {
                        if (otherHeader !== header && otherHeader.classList.contains('active')) {
                            otherHeader.classList.remove('active');
                            otherHeader.nextElementSibling.classList.remove('active');
                        }
                    });

                    // Se stiamo chiudendo, aspetta che l'animazione finisca
                    if (isClosing) {
                        products.style.maxHeight = products.scrollHeight + 'px';
                        // Forza un reflow
                        products.offsetHeight;
                        products.style.maxHeight = '0px';

                        setTimeout(() => {
                            header.classList.remove('active');
                            products.classList.remove('active');
                            products.style.maxHeight = '';
                        }, 300); // Deve corrispondere alla durata della transizione CSS
                    } else {
                        header.classList.add('active');
                        products.classList.add('active');
                        products.style.maxHeight = products.scrollHeight + 'px';

                        setTimeout(() => {
                            products.style.maxHeight = '';
                        }, 400);
                    }
                });
            });
        });
    </script>

</body>

</html>