<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package myfossil2017
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
        <div class="container">
            <div class="row top" id="nav">
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <img id="fossil-logo" src="<?=get_template_directory_uri() ?>/images/myfossil-logo-white-small.png" alt="myFOSSIL Logo" />
                </div>
                <div class="hidden-xs hidden-sm hidden-md col-md-6 col-lg-6" id="footer-logos">
                    <div class="col-xs-12 col-md-3">
                        <a href="http://ufl.edu" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/images/uf.png" />
                        </a>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <a href="https://www.flmnh.ufl.edu/" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/images/flmnh.png" />
                        </a>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <a href="http://www.nsf.gov/" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/images/nsf.png" />
                        </a>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <a href="http://www.atmosphereapps.com/" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/images/atmo.png" />
                        </a>
                    </div>
                </div>
            </div><!-- .row -->

            <div class="row">
                <div id="footer-disclaimer" class="col-xs-12 col-lg-12">
                    <p>
                    Development of myFOSSIL is based upon work largely
                    supported by the National Science Foundation under Grant
                    No. DRL-1322725. Any opinions, findings, and conclusions or
                    recommendations expressed in this material are those of the
                    authors and do not necessarily reflect the views of the
                    National Science Foundation.
                    <br />
                    <br />
                    <a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/">Creative Commons Attribution-NonCommercial 4.0 International License</a>.
                    </p>
                </div><!-- column -->
            </div><!-- .row -->
        </div><!-- .container -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
