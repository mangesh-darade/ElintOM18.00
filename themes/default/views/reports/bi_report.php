<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="clearfix"></div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="box" style="border: 1px solid #eee;">
                                        <div class="box-header" style="background: #f9f9f9;">
                                            <h2 class="blue">
                                                <i class="fa fa-bar-chart"></i> <?= $page_title ?>
                                            </h2>
                                        </div>
                                        <div class="box-content" style="padding: 15px;">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="table-responsive">
                                                        <div class="embed-responsive embed-responsive-16by9">
                                                            <iframe class="embed-responsive-item" src="<?= $report_url ?>" frameborder="0" allowfullscreen></iframe>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Set the height of the iframe to be 80% of the viewport height minus the header
        function resizeIframe() {
            var windowHeight = $(window).height();
            var headerHeight = $('.box-header').outerHeight();
            var iframeHeight = windowHeight - headerHeight - 100; // 100px for padding and margins
            $('.embed-responsive').css('min-height', iframeHeight + 'px');
        }
        
        // Resize on load and window resize
        resizeIframe();
        $(window).resize(function() {
            resizeIframe();
        });
    });
</script>
