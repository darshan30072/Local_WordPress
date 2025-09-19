jQuery(document).ready(function ($) {
    let currentPage = 1;
    let currentCategory = "";

    function loadProducts(page = 1, category = "") {
        $("#products-container").html("<p>Loading...</p>");
        $("#products-pagination").html("");

        $.getJSON(ProductsAPI.endpoint, { paged: page, category: category }, function (response) {
            let productsHTML = "";

            if (response.products.length > 0) {
                $.each(response.products, function (i, product) {
                    productsHTML += `
                        <div class="col-md-4 product-item mb-4">
                            <div class="card h-100">
                                <a href="${product.permalink}">
                                    <img src="${product.image}" class="card-img-top" alt="${product.title}">
                                </a>
                                <div class="card-body">
                                    <h3 class="card-title">
                                        <a href="${product.permalink}">${product.title}</a>
                                    </h3>
                                    <p class="card-text">${product.description}</p>
                                    <p class="card-price"><strong>Price:</strong> ${product.price}</p>
                                </div>
                            </div>
                        </div>`;
                });
            } else {
                productsHTML = "<p>No products found.</p>";
            }

            $("#products-container").html(productsHTML);

            // Pagination
            if (response.pagination.total_pages > 1) {
                let paginationHTML = "";

                if (page > 1) {
                    paginationHTML += `<a href="#" class="page-link" data-page="${page - 1}">« Prev</a>`;
                }

                for (let i = 1; i <= response.pagination.total_pages; i++) {
                    let activeClass = i === page ? "active" : "";
                    paginationHTML += `<a href="#" class="page-link ${activeClass}" data-page="${i}">${i}</a>`;
                }

                if (page < response.pagination.total_pages) {
                    paginationHTML += `<a href="#" class="page-link" data-page="${page + 1}">Next »</a>`;
                }

                $("#products-pagination").html(paginationHTML);
            }
        });
    }

    // Category filter click
    $("#products-filters").on("click", ".nav-link", function () {
        $("#products-filters .nav-link").removeClass("active");
        $(this).addClass("active");

        currentCategory = $(this).data("category");
        currentPage = 1;
        loadProducts(currentPage, currentCategory);
    });

    // Pagination click
    $("#products-pagination").on("click", ".page-link", function (e) {
        e.preventDefault();
        currentPage = parseInt($(this).data("page"));
        loadProducts(currentPage, currentCategory);
    });

    // Load initial products
    loadProducts();
});
