<!-- Standard Activity Module -->
<?php if(pt_main_module_available('activity')){ ?>
<div class="col-md-12 row5">
    <div class="form-group">
        <h2 class="main-title go-right">Activity</h2>
        <div class="clearfix"></div>
        <i class="tiltle-line go-right"></i>
    </div>
</div>
<?php foreach($featuredActivity as $item){ ?>
<div class="col-md-3 row5">
    <a href="<?php echo $item->slug;?>">
        <div class="featured">
            <div class="col-xs-12 go-right wow fadeIn">
                <div class="row">
                    <div class="load">
                        <img class="img-responsive lazy" <?php echo $lazy; ?> data-lazy="<?php echo $item->thumbnail;?>" />
                        <img class="overlay" src="<?php echo $theme_url; ?>assets/img/overlay.png" style="z-index: 3">
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if($item->price > 0){ ?>
            <div class="text-center featured-price">
                <div class="text-center">
                    <small><?php echo $item->currCode;?></small> <?php echo $item->currSymbol; ?><?php echo $item->price;?>
                </div>
            </div>
            <?php } ?>
            <div class="col-xs-12 go-right wow fadeIn featured-title">
                <div class="p5">
                    <div class="strong"><?php echo character_limiter($item->title,25);?></div>
                    <?php echo $item->stars;?>
                    <div class=""><i class="icon-location-6 go-right"></i> <?php echo character_limiter($item->location,20);?></div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php } ?>
<div class="col-md-12">
    <hr>
</div>
<?php } ?>
<!-- End Activity List Module -->


<!-- Start Entertainment Module -->
<?php if(pt_main_module_available('entertainment')){ ?>
<div class="col-md-12 row5">
    <div class="form-group">
        <h2 class="main-title go-right">Entertainment</h2>
        <div class="clearfix"></div>
        <i class="tiltle-line go-right"></i>
    </div>
</div>
<?php foreach($featuredEntertainment as $item){ ?>
<div class="col-md-3 row5">
    <a href="<?php echo $item->slug;?>">
        <div class="featured">
            <div class="col-xs-12 go-right wow fadeIn">
                <div class="row">
                    <div class="load">
                        <img class="img-responsive lazy" <?php echo $lazy; ?> data-lazy="<?php echo $item->thumbnail;?>" />
                        <img class="overlay" src="<?php echo $theme_url; ?>assets/img/overlay.png" style="z-index: 3">
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if($item->price > 0){ ?>
            <div class="text-center featured-price">
                <div class="text-center">
                    <small><?php echo $item->currCode;?></small> <?php echo $item->currSymbol; ?><?php echo $item->price;?>
                </div>
            </div>
            <?php } ?>
            <div class="col-xs-12 go-right wow fadeIn featured-title">
                <div class="p5">
                    <div class="strong"><?php echo character_limiter($item->title,25);?></div>
                    <?php echo $item->stars;?>
                    <div class=""><i class="icon-location-6 go-right"></i> <?php echo character_limiter($item->location,20);?></div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php } ?>
<div class="col-md-12">
    <hr>
</div>
<?php } ?>
<!-- End Entertainment Module -->

<!-- Start Restaurant Module -->
<?php if(pt_main_module_available('restaurant')){ ?>
<div class="col-md-12 row5">
    <div class="form-group">
        <h2 class="main-title go-right">Restaurant</h2>
        <div class="clearfix"></div>
        <i class="tiltle-line go-right"></i>
    </div>
</div>
<?php foreach($featuredRestaurant as $item){ ?>
<div class="col-md-3 row5">
    <a href="<?php echo $item->slug;?>">
        <div class="featured">
            <div class="col-xs-12 go-right wow fadeIn">
                <div class="row">
                    <div class="load">
                        <img class="img-responsive lazy" <?php echo $lazy; ?> data-lazy="<?php echo $item->thumbnail;?>" />
                        <img class="overlay" src="<?php echo $theme_url; ?>assets/img/overlay.png" style="z-index: 3">
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if($item->price > 0){ ?>
            <div class="text-center featured-price">
                <div class="text-center">
                    <small><?php echo $item->currCode;?></small> <?php echo $item->currSymbol; ?><?php echo $item->price;?>
                </div>
            </div>
            <?php } ?>
            <div class="col-xs-12 go-right wow fadeIn featured-title">
                <div class="p5">
                    <div class="strong"><?php echo character_limiter($item->title,25);?></div>
                    <?php echo $item->stars;?>
                    <div class=""><i class="icon-location-6 go-right"></i> <?php echo character_limiter($item->location,20);?></div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php } ?>
<div class="col-md-12">
    <hr>
</div>
<?php } ?>
<!-- End Restaurant Module -->

<!-- Start Spa Module -->
<?php if(pt_main_module_available('spa')){ ?>
<div class="col-md-12 row5">
    <div class="form-group">
        <h2 class="main-title go-right">Spa</h2>
        <div class="clearfix"></div>
        <i class="tiltle-line go-right"></i>
    </div>
</div>
<?php foreach($featuredSpa as $item){ ?>
<div class="col-md-3 row5">
    <a href="<?php echo $item->slug;?>">
        <div class="featured">
            <div class="col-xs-12 go-right wow fadeIn">
                <div class="row">
                    <div class="load">
                        <img class="img-responsive lazy" <?php echo $lazy; ?> data-lazy="<?php echo $item->thumbnail;?>" />
                        <img class="overlay" src="<?php echo $theme_url; ?>assets/img/overlay.png" style="z-index: 3">
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if($item->price > 0){ ?>
            <div class="text-center featured-price">
                <div class="text-center">
                    <small><?php echo $item->currCode;?></small> <?php echo $item->currSymbol; ?><?php echo $item->price;?>
                </div>
            </div>
            <?php } ?>
            <div class="col-xs-12 go-right wow fadeIn featured-title">
                <div class="p5">
                    <div class="strong"><?php echo character_limiter($item->title,25);?></div>
                    <?php echo $item->stars;?>
                    <div class=""><i class="icon-location-6 go-right"></i> <?php echo character_limiter($item->location,20);?></div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php } ?>
<div class="col-md-12">
    <hr>
</div>
<?php } ?>
<!-- End Spa Module -->

<!-- Start Wedding Module -->
<?php if(pt_main_module_available('wedding')){ ?>
<div class="col-md-12 row5">
    <div class="form-group">
        <h2 class="main-title go-right">Wedding</h2>
        <div class="clearfix"></div>
        <i class="tiltle-line go-right"></i>
    </div>
</div>
<?php foreach($featuredWedding as $item){ ?>
<div class="col-md-3 row5">
    <a href="<?php echo $item->slug;?>">
        <div class="featured">
            <div class="col-xs-12 go-right wow fadeIn">
                <div class="row">
                    <div class="load">
                        <img class="img-responsive lazy" <?php echo $lazy; ?> data-lazy="<?php echo $item->thumbnail;?>" />
                        <img class="overlay" src="<?php echo $theme_url; ?>assets/img/overlay.png" style="z-index: 3">
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if($item->price > 0){ ?>
            <div class="text-center featured-price">
                <div class="text-center">
                    <small><?php echo $item->currCode;?></small> <?php echo $item->currSymbol; ?><?php echo $item->price;?>
                </div>
            </div>
            <?php } ?>
            <div class="col-xs-12 go-right wow fadeIn featured-title">
                <div class="p5">
                    <div class="strong"><?php echo character_limiter($item->title,25);?></div>
                    <?php echo $item->stars;?>
                    <div class=""><i class="icon-location-6 go-right"></i> <?php echo character_limiter($item->location,20);?></div>
                </div>
            </div>
        </div>
    </a>
</div>
<?php } ?>
<div class="col-md-12">
    <hr>
</div>
<?php } ?>
<!-- End Wedding Module -->
