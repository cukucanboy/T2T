<div class="panel-body">
  <?php if(!empty($points)){ ?>
  <?php $count = 0; foreach($points as $r){ $count++;  ?>
  <div id="point-<?php echo $r->rowids;?>">
  <div class="col-md-5 offset-0">
    <img alt="" class="left mr20 img-responsive" style="max-width:96px" src="<?php echo $wl->thumbnail;?>">
    <span class="dark size12">Service : <?php echo $r->shop_code;?></span><br>
    <span class="dark size12">Amount :<?php echo $r->amount;?></span><br>
    <span class="dark size12">Point Reward : <?php echo $r->point_reward;?></span><br>
    <small><span class="grey">Service Date : <?php echo $r->service_date;?></span></small><br>
  </div>
  <div class="col-md-2 offset-0">
  </div>
  <div class="col-md-5 offset-0">

    <div class="clearfix"></div>
    <div style="margin:5px"></div>
    <div class="clearfix"></div>
    <span class="btn btn-sm btn-block btn-danger removewish remove_btn" id="<?php echo $r->rowids;?>">  <?php echo trans('0108');?></span>
  </div>
  <div class="clearfix"></div>
  <div class="line2"></div>
  </div>
  <br>
  <?php } }else{  ?>
  <h4><?php echo trans('0110');?></h4>
  <?php } ?>
</div>
