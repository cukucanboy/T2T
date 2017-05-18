<section class="masonry-section">
    <div class="container">
        <div class="destination-grid-content">
            <div class="section-title">
                <h3>More than <a href="destinations-list.html">238 Destinations</a> is waiting</h3>
            </div>
            <div class="row">
                <div class="awe-masonry">
                    <!-- GALLERY ITEM -->
                    <?php  if($sliderInfo->totalSlides > 0){ foreach($sliderInfo->slides as $ms){ ?>
                    <div class="awe-masonry__item">
                        <a href="#">
                            <div class="image-wrap image-cover">
                                <img src="<?php echo $ms->thumbnail;?>" alt="">
                            </div>
                        </a>
                        <div class="item-title">
                            <h2><a href="#"><?php echo $ms->title;?></a></h2>
                            <div class="item-cat">
                                <ul>
                                    <li><a href="#">Italy</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="item-available">
                            <span class="count">845</span>
                            available hotel
                        </div>
                    </div>
      <?php }} ;?>
                    <!-- END / GALLERY ITEM -->

                </div>
            </div>
            <div class="more-destination">
                <a href="#">More Destinations</a>
            </div>
        </div>
    </div>
</section>
