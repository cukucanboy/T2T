<style>
  .item { max-height: 55px !important; }
  .parallax-window { min-height: 220px; position: relative; }
</style>
<link rel="stylesheet" href="<?php echo $theme_url; ?>assets/css/destination.css" />
<section style="max-height:200px !important" class="parallax-window" data-parallax="scroll" data-image-src="<?php echo $theme_url; ?>assets/img/login.jpg" data-natural-width="150" data-natural-height="100">
  <div class="parallax-content-1">
    <div class="animated fadeInDown">
      <h1 style="margin-top: -216px;"><?php echo trans('Destination');?></h1>
      <p><?php echo trans('0481');?></p>
    </div>
  </div>
</section>
<!-- End section -->
<?php //print_r($alldestinations); ?>
<div class="container margin_60">
  <div class="row">
    <div class="col-md-8 go-right">
      <div class="panel panel-default">
        <div class="panel-heading title_rtl"><?php  if($ptype == "search"){
          echo trans('0291');
          }elseif($ptype == "category"){
          echo trans('0292')." - ".$categoryname;
          }else{
           echo trans('0285');
          }  ?></div>
        <div class="panel-body">
          <?php if(!empty($alldestinations['all'])){
            foreach($alldestinations['all'] as $post):
  $destinationslib->set_id($post->destination_id);
  $destinationslib->destination_short_details();
             ?>
          <div class="col-md-4 go-right">
            <div class="row">
              <a href="<?php echo base_url().'destinations/'.$post->destination_slug;?>"><img src="<?php echo pt_destination_thumbnail($post->destination_id); ?>" alt="<?php echo $destinationlib->title;?>" class="img-responsive"></a>
            </div>
          </div>
          <div class="col-md-8">
            <a href="<?php echo base_url().'destinations/'.$post->destination_slug;?>">
              <h3 class="go-right RTL mtb0"><?php echo $destinationlib->title;?></h3>
            </a>
            <div class="clearfix"></div>
            <div class="destination_info clearfix">
              <div class="post-left go-right">
                <ul class="go-right">
                  <li><i class="icon-calendar-empty"></i><?php echo trans('0480');?> <span class=""><?php echo $destinationlib->date; ?></span></li>
                </ul>
              </div>
            </div>
            <p class="RTL"> <?php echo character_limiter(strip_tags($destinationlib->desc), 120);?></p>
            <div class="clearfix"></div>
            <!--<a class="btn btn-success go-right" href="<?php echo base_url().'destinations/'.$post->destination_slug;?>"> <?php echo trans('0286');?> </a>-->
            <div class="clearfix">
            </div>
          </div>
          <div class="clearfix"></div>
          <hr>
          <?php endforeach; }else{ echo '<h1 class="text-center">' . trans("066") . '</h1>'; } ?>
        </div>
      </div>
      <ul class="nav nav-pills nav-justified" role="tablist">
        <?php echo createPagination($info);?>
      </ul>
      <br /><br /><br />
    </div>
    <!-- End col-md-8-->
    <?php include('sidebar.php'); ?>
  </div>
  <!-- End row-->
</div>
<!-- End container -->
