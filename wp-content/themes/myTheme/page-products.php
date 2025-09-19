<?php

/**
 * Template Name: Products  
 */
get_header();
?>

<?php the_content(); ?>
<main class="site-content container">
    <h1 class="page-title"><?php the_title(); ?></h1>

    <!-- 🔹 Category Filter -->
    <div style="margin:20px 0;">
        <label for="categoryFilter"><strong>Filter by Category:</strong></label>
        <select id="categoryFilter" style="padding:6px; border-radius:5px; margin-left:10px;">
            <option value="">All</option>
            <?php
            $categories = get_terms([
                'taxonomy'   => 'products_category',
                'hide_empty' => true,
            ]);
            if (!empty($categories) && !is_wp_error($categories)) {
                foreach ($categories as $cat) {
                    echo '<option value="' . esc_attr($cat->slug) . '">' . esc_html($cat->name) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <!-- 🔹 Products List (Grid 3x2) -->
    <div id="products-list"
        style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin:50px 0;">
        <p>Loading products...</p>
    </div>

    <!-- 🔹 Pagination -->
    <div id="pagination" style="margin:20px 0; text-align:center;"></div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const productsList = document.getElementById("products-list");
        const pagination = document.getElementById("pagination");
        const categoryFilter = document.getElementById("categoryFilter");

        let currentPage = 1;
        let currentCategory = "";

        function fetchProducts(page = 1, category = "") {
            productsList.innerHTML = "<p>Loading products...</p>";

            let url = `<?php echo site_url('/wp-json/mytheme/v1/products'); ?>?page=${page}&limit=6`;
            if (category) {
                url += `&category=${category}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    productsList.innerHTML = "";

                    if (data.products.length === 0) {
                        productsList.innerHTML = "<p>No products found.</p>";
                        return;
                    }

                    data.products.forEach(product => {
                        const card = document.createElement("div");
                        card.className = "product-card";
                        card.style.border = "1px solid #ddd";
                        card.style.padding = "15px";
                        card.style.borderRadius = "8px";
                        card.style.background = "#fff";
                        card.style.textAlign = "center";

                        card.innerHTML = `
                        <img src="${product.image?.url || ''}" alt="${product.image?.alt || product.title}" style="max-width:100%; height:auto; margin-bottom:10px;" />
                        <h3>${product.title}</h3>
                        <p>${product.description || ""}</p>
                        <strong>Price: $${product.price || "N/A"}</strong><br/>
                        <a href="${product.permalink}" style="display:inline-block; margin-top:10px; padding:6px 12px; background:#0073aa; color:#fff; border-radius:5px; text-decoration:none;">View</a>
                    `;

                        productsList.appendChild(card);
                    });

                    // Pagination
                    pagination.innerHTML = "";
                    if (data.pages > 1) {
                        for (let i = 1; i <= data.pages; i++) {
                            const btn = document.createElement("button");
                            btn.innerText = i;
                            btn.style.margin = "0 5px";
                            btn.style.padding = "6px 12px";
                            btn.style.borderRadius = "5px";
                            btn.style.border = i === data.current ? "2px solid #0073aa" : "1px solid #ccc";
                            btn.style.background = i === data.current ? "#0073aa" : "#f1f1f1";
                            btn.style.color = i === data.current ? "#fff" : "#333";

                            btn.addEventListener("click", () => {
                                currentPage = i;
                                fetchProducts(i, currentCategory);
                            });

                            pagination.appendChild(btn);
                        }
                    }
                })
                .catch(err => {
                    productsList.innerHTML = "<p>Error loading products.</p>";
                    console.error(err);
                });
        }

        // 🔹 Handle Category Change
        categoryFilter.addEventListener("change", () => {
            currentCategory = categoryFilter.value;
            currentPage = 1;
            fetchProducts(currentPage, currentCategory);
        });

        // Initial Load
        fetchProducts(currentPage, currentCategory);
    });
</script>

<?php get_footer(); ?>