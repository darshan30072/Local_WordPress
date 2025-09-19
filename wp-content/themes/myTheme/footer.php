<!-- Start Footer -->
<footer class=" bg-black" id="tempaltemo_footer">
    <div class="container p-0">
        <div class="row justify-content-between text-light py-1">
            <div class="col-auto align-content-center">
                <ul class="list-inline text-left footer-icons m-0">
                    <li class="list-inline-item border border-light rounded-circle text-center">
                        <a class="text-light text-decoration-none" target="_blank" href="http://facebook.com/"><i class="fab fa-facebook-f fa-lg fa-fw"></i></a>
                    </li>
                    <li class="list-inline-item border border-light rounded-circle text-center">
                        <a class="text-light text-decoration-none" target="_blank" href="https://www.instagram.com/"><i class="fab fa-instagram fa-lg fa-fw"></i></a>
                    </li>
                    <li class="list-inline-item border border-light rounded-circle text-center">
                        <a class="text-light text-decoration-none" target="_blank" href="https://twitter.com/"><i class="fab fa-twitter fa-lg fa-fw"></i></a>
                    </li>
                    <li class="list-inline-item border border-light rounded-circle text-center">
                        <a class="text-light text-decoration-none" target="_blank" href="https://www.linkedin.com/"><i class="fab fa-linkedin fa-lg fa-fw"></i></a>
                    </li>
                </ul>
            </div>
            <div class="col-auto">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'footer-menu',
                        'container'      => false,
                        'menu_class'     => 'footer-nav',
                        'fallback_cb'    => false,
                    )
                );
                ?>
            </div>
            <div class="col-auto align-content-center">
                <div class="container p-0">
                    <div class="row pt-2 text-center">
                        <div class="col-12">
                            <p class="text-left text-light">
                                Copyright &copy; 2025 Wappzo
                                | Designed by <a rel="sponsored" href="#" target="_blank">Darshan Tandel</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>
<!-- End Footer -->

<!-- Start Script -->
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/jquery-1.11.0.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/templatemo.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/custom.js"></script>
<!-- End Script -->

<?php wp_footer(); ?>
</body>

</html>