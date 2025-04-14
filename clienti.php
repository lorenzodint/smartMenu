<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$menu = getMenu("");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .category {
            margin-bottom: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow: hidden;
        }

        .category-header {
            background: #f8f8f8;
            padding: 15px;
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: all 0.3s ease;
        }

        .category-header:hover {
            background: #f0f0f0;
        }

        .category-header h2 {
            color: #2c3e50;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .category-header h2::after {
            content: '▼';
            font-size: 0.8em;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .category-header.active h2::after {
            transform: rotate(180deg);
        }

        .products {
            background: white;
            padding: 0 15px;
        }

        .product {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        /* Classi dedicate alle animazioni */
        .categoria-animazione {
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                        padding 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .categoria-animazione.active {
            max-height: 2000px;
            padding: 15px;
        }

        .prodotto-animazione {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transition-delay: 0.1s;
        }

        .categoria-animazione.active .prodotto-animazione {
            opacity: 1;
            transform: translateY(0);
        }

        /* Delays progressivi per i prodotti */
        .categoria-animazione.active .prodotto-animazione:nth-child(2) { transition-delay: 0.2s; }
        .categoria-animazione.active .prodotto-animazione:nth-child(3) { transition-delay: 0.3s; }
        .categoria-animazione.active .prodotto-animazione:nth-child(4) { transition-delay: 0.4s; }
        .categoria-animazione.active .prodotto-animazione:nth-child(5) { transition-delay: 0.5s; }

        /* Animazioni keyframe */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-15px);
            }
        }

        .categoria-animazione.active {
            animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .categoria-animazione:not(.active) {
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Aggiungiamo un effetto di evidenziazione per la categoria attiva */
        .category-header.active {
            background: #f0f0f0;
            border-bottom: 2px solid #2c3e50;
        }

        /* Effetto hover migliorato */
        .category-header:hover:not(.active) {
            background: #f4f4f4;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product:last-child {
            border-bottom: none;
        }

        .product h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .price {
            color: #2c3e50;
            font-weight: bold;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Menu</h1>
        
        <?php if ($menu): ?>
            <?php 
            usort($menu['categories'], function($a, $b) {
                return $a['position'] - $b['position'];
            });
            
            foreach ($menu['categories'] as $category): 
                if (!$category['visible']) continue;
            ?>
                <div class="category">
                    <div class="category-header">
                        <h2><?= htmlspecialchars($category['name']) ?></h2>
                    </div>
                    
                    <div class="products categoria-animazione">
                        <?php 
                        usort($category['products'], function($a, $b) {
                            return $a['position'] - $b['position'];
                        });
                        
                        foreach ($category['products'] as $product): 
                            if (!$product['visible']) continue;
                        ?>
                            <div class="product prodotto-animazione">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="description"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="price">€<?= number_format($product['price'], 2) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Menu non disponibile</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryHeaders = document.querySelectorAll('.category-header');
            
            categoryHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const wasActive = header.classList.contains('active');
                    const products = header.nextElementSibling;
                    
                    // Chiudi tutti gli accordion
                    categoryHeaders.forEach(otherHeader => {
                        const otherProducts = otherHeader.nextElementSibling;
                        if (otherHeader !== header && otherHeader.classList.contains('active')) {
                            otherHeader.classList.remove('active');
                            otherProducts.classList.remove('active');
                        }
                    });

                    // Toggle della categoria cliccata
                    header.classList.toggle('active');
                    products.classList.toggle('active');
                });
            });

            // Apri la prima categoria con animazione
            if (categoryHeaders.length > 0) {
                setTimeout(() => {
                    categoryHeaders[0].classList.add('active');
                    categoryHeaders[0].nextElementSibling.classList.add('active');
                }, 300);
            }
        });
    </script>
</body>
</html>


