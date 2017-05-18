<script type="text/javascript">
$(function(){
   $(".inline").colorbox({inline:true, width:"50%"});

$(".advsearch").on("click",function() {
var package = $("#package").val();
var category = $("#category").val();
var ownedby = $("#ownedby").val();
var ttype = $("#type").val();
var status = $("#status").val();
var weddingtitle = $("#weddingtitle").val();

    var perpage = $("#perpage").val();

     $(".loadbg").css("display","block");

     var dataString = 'advsearch=1&package='+package+'&perpage='+perpage+'&category='+category+'&ownedby='+ownedby+'&ttype='+ttype+'&status='+status+'&weddingtitle='+weddingtitle;

    $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>wedding/weddingback/wedding_ajax/",
           data: dataString,
           cache: false,
           success: function(result){
               $(".loadbg").css("display","none");
                 $("#ajax-data").html(result);
                  $("#li_1").addClass("active");
           }
      });


});
// search wedding
 $(".searchdata").keypress(function(e) {
    if(e.which == 13) {
    var sterm = $(this).val();
    var perpage = $("#perpage").val();
  if($.trim(sterm).length < 1){ }else{
    $(".loadbg").css("display","block");
   var dataString = 'search=1&searchdata='+sterm+'&perpage='+perpage;
   $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>wedding/weddingback/wedding_ajax/",
           data: dataString,
           cache: false,
           success: function(result){
             $(".loadbg").css("display","none");
           $("#ajax-data").html(result);
                 $("#ajax-data").html(result);
                  $("#li_1").addClass("active");
           }
      });
     }
   }
  });
  $("#li_1").addClass('active');

      //delete single wedding
    $(".del_single").click(function(){
   var id = $(this).attr('id');

  $.alert.open('confirm', 'Are you sure you want to Delete it', function(answer) {
    if (answer == 'yes')
    $.post("<?php echo base_url();?>wedding/weddingajaxcalls/delete_single_wedding", { weddingid: id }, function(theResponse){
    location.reload();
  	});

  });
  });
 // end delete single wedding
 // delete selected wedding
  $('.del_selected').click(function(){
      var wedding = new Array();
      $("input:checked").each(function(){ wedding.push($(this).val()); });
      var count_checked = $("[name='wedding_ids[]']:checked").length;
      if(count_checked == 0) { $.alert.open('info', 'Please select a Tour to Delete.'); return false; }
      $.alert.open('confirm', 'Are you sure you want to Delete it', function(answer) {
     if (answer == 'yes')
     $.post("<?php echo base_url();?>wedding/weddingajaxcalls/delete_multiple_wedding", { weddinglist: wedding }, function(theResponse){
     location.reload();
  	}); }); });
 // end delete selected wedding

 // Disable selected wedding
  $('.disable_selected').click(function(){
   var wedding = new Array();
   $("input:checked").each(function() { wedding.push($(this).val()); });
   var count_checked = $("[name='wedding_ids[]']:checked").length;
   if(count_checked == 0) { $.alert.open('info', 'Please select a Tour to Disable.'); return false; }
   $.alert.open('confirm', 'Are you sure you want to Disable it', function(answer) {
   if (answer == 'yes')
   $.post("<?php echo base_url();?>wedding/weddingajaxcalls/disable_multiple_wedding", { weddinglist: wedding }, function(theResponse){
   location.reload();
   }); }); });
  // end disable single wedding
  // Enable selected Wedding
  $('.enable_selected').click(function(){
   var wedding = new Array();
   $("input:checked").each(function() { wedding.push($(this).val()); });
   var count_checked = $("[name='wedding_ids[]']:checked").length;
   if(count_checked == 0) { $.alert.open('info', 'Please select a Tour to Enable.'); return false; }
   $.alert.open('confirm', 'Are you sure you want to Enable it', function(answer) {
   if (answer == 'yes')
   $.post("<?php echo base_url();?>wedding/weddingajaxcalls/enable_multiple_wedding", { weddinglist: wedding }, function(theResponse){
   location.reload();
   }); }); });
   // end enable selected wedding
   // start update featured status
    $(".updatefeatured").click(function(){
      var weddingid = $(this).attr('id'); var isfeatured = $("#isfeatured_"+weddingid).val();
      var checkwhen = $("#whendata_"+weddingid).val();
      if(isfeatured == '1'){
        if(checkwhen == "bydate"){
         var ffrom = $("#dfrom_"+weddingid).val();
         var fto = $("#dto_"+weddingid).val();
         var newtitle = "Featured "+ffrom+" to "+fto;
         if(ffrom == '' || fto == ''){
         alert('Kinldy select date'); return false;
         }else{ $("#feat_"+weddingid).attr('title', newtitle).tooltip('fixTitle');
        $("#feat_"+weddingid).addClass('fa-star');
        $("#feat_"+weddingid).removeClass('fa-star-o');
        return true; } }

    $("#feat_"+weddingid).attr('title', 'Featured Forever').tooltip('fixTitle');
    $("#feat_"+weddingid).addClass('fa-star');
    $("#feat_"+weddingid).removeClass('fa-star-o');
    }else{
    $("#feat_"+weddingid).attr('title', 'Not Featured').tooltip('fixTitle');
    $("#feat_"+weddingid).removeClass('fa-star');
    $("#feat_"+weddingid).addClass('fa-star-o');
    }
    });
    // End update featured status stars
    //start ajax form for featured status submit
    var options = {   beforeSend: function(){ }, uploadProgress: function(event, position, total, percentComplete)
    { }, success: function(){ }, complete: function(response){
    if($.trim(response.responseText) == "done"){
    $(".output").html('please Wait...');
    parent.jQuery.fn.colorbox.close(); } }, target: '.output' };
    $('.my-form').submit(function(){
    $(this).ajaxSubmit(options);
    return false; });
    // end ajax form submit


}) // end document ready

// wedding updateorder function
 function updateOrder(order,id,olderval){
   var maximumorder = <?php echo $max['nums'];?>;
   if(order != olderval){
   if(order > maximumorder || order < 1){
   $.alert.open('info', 'Cannot assign order lesser than 1 or greater than '+maximumorder);
   $("#order_"+id).val(olderval);
   }else{ $('#pt_reload_modal').modal('show');
           $.post("<?php echo base_url();?>wedding/weddingajaxcalls/update_wedding_order", { order: order,id: id }, function(theResponse){
           $('#pt_reload_modal').modal('hide');	}); } } }
//end wedding updateorder function

//start forever select option
  function foreverOpt(option,id){
  if(option == '1'){ $('#whendata_'+id).removeAttr('disabled'); }else{
        $('#whendata_'+id).attr('disabled','disabled');
        $("#dfrom_"+id).attr('disabled','disabled');
        $("#dto_"+id).attr('disabled','disabled');
          } }
//end forever select option

// start featured date settings
  function fdateOpt(option,id){
  if(option == 'bydate'){
        $("#dfrom_"+id).removeAttr('disabled');
        $("#dto_"+id).removeAttr('disabled');
        }else{
        $("#dfrom_"+id).attr('disabled','disabled');
        $("#dto_"+id).attr('disabled','disabled');
        } }
// end featured date settings

// Start Change Pagination
function changePagination(pageId,liId){
   $(".loadbg").css("display","block");
   var perpage = $("#perpage").val();
   var last = $(".last").prop('id');
   var prev = pageId - 1;
   var next =  parseFloat(liId) + 1;
   var dataString = 'perpage='+ perpage;
   $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>wedding/weddingback/wedding_ajax/"+pageId,
           data: dataString,
           cache: false,
           success: function(result){
             $(".loadbg").css("display","none"); $("#ajax-data").html(result);
             if(liId != '1'){
             $(".first").after("<li class='previous'><a href='javascript:void(0)' onclick=changePagination('"+prev+"','"+prev+"') >Previous</a></li>");
             }
             $(".litem").removeClass("active"); $("#li_"+liId).css("display","block"); $("#li_"+liId).addClass("active");
             } }); }
// End Change Pagination

// Start change pagination by per page
function changePerpage(perpage){
    $(".loadbg").css("display","block"); var dataString = 'perpage='+ perpage;
    $.ajax({
           type: "POST",
           url: "<?php echo base_url();?>wedding/weddingback/wedding_ajax/1",
           data: dataString,
           cache: false,
           success: function(result){
             $(".loadbg").css("display","none");
             $("#ajax-data").html(result);
             $("#li_1").addClass("active");
           } }); }
// End change pagination by per page
</script>

  <?php if($this->session->flashdata('flashmsgs')){ echo NOTIFY; } echo LOAD_BG;?>
    <?php if(empty($ajaxreq)){ ?>
<div class="<?php echo body;?>">
  <div class="panel panel-primary table-bg">
    <div class="panel-heading">
      <span class="panel-title pull-left"><i class="fa fa-suitcase"></i> Wedding Management</span>
      <div class="pull-right">
        <?php if($adminsegment != 'supplier'){ ?> <a data-toggle="modal" href="<?php echo base_url().$adminsegment;?>/wedding/add/"><?php echo PT_ADD; ?></a><?php } ?>
        <span class="del_selected">   <?php echo PT_DEL_SELECTED; ?></span>
        <span class="disable_selected">   <?php echo PT_DIS_SELECTED; ?></span>
        <span class="enable_selected">   <?php echo PT_ENA_SELECTED; ?></span>
        <?php echo PT_BACK; ?>
      </div>
      <div class="clearfix"></div>
    </div>


    <div class="panel-body">

         <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title">
        <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#filter" aria-expanded="false" aria-controls="filter">
          Search/Filter
        </a>
      </h4>
    </div>
    <div id="filter" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
      <div class="panel-body">





      <div class="form-horizontal">
         <div class="form-group">

      								   <label class="col-md-2 control-label">Tour Category</label>
      									 <div class="col-md-3">

                                      <select class="form-control type" name="" id="category">
                                      <option value="">Any</option>
                                      <?php
                                      if(!empty($weddingcategories)){
                                      foreach($weddingcategories as $tcats){?>
                                      <option value="<?php echo $tcats->sett_id;?>"><?php echo $tcats->sett_name;?></option>
                                      <?php  } } ?>


                                      </select>
      									</div>

                                            <label class="col-md-2 control-label">Tour Type</label>
      									 <div class="col-md-3">

                                           <select name="" class="form-control" id="type">
                                            <option value="">Any</option>
                                            <?php
                                            if(!empty($weddingtypes)){
                                            foreach($weddingtypes as $tt){?>
                                           <option value="<?php echo $tt->sett_id;?>"><?php echo $tt->sett_name;?></option>
                                            <?php  } } ?>

                                           </select>

      									</div>




                                         </div>
      <div class="form-group">
      								   <label class="col-md-2 control-label">Status</label>
      									 <div class="col-md-3">

                                        <select class="form-control type" name="" id="status">
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>


                                        </select>
      									</div>

      								   <label class="col-md-2 control-label">Tour Name</label>
      									 <div class="col-md-3">

      											<input class="form-control" type="text" placeholder="Tour Name" id="weddingtitle" name="" value="">

      									</div>

                                        </div>



                                      <div class="form-group">


                                         <label class="col-md-2 control-label">Owned By</label>
      									 <div class="col-md-3">

                                           <select name="" class="form-control" id="" onchange="showNames(this.options[this.selectedIndex].value)">
                                           <option value="">Anyone</option>
                                           <option value="webadmin">Admin</option>
                                           <option value="subadmins">Sub Admin</option>
                                           <option value="supplier">Supplier</option>

                                           </select>
      									</div>

                                        <label class="col-md-2 control-label">Name</label>
      									 <div class="col-md-3">

                                   <select name="" class="chosen-select" id="ownedby">
                                           <option value="">Select</option>

                                           </select>
      									</div>
      									</div>


                                        <div class="form-group">

                                         <label class="col-md-2 control-label">Tour Package</label>
      									 <div class="col-md-3">

                                           <select name="" class="form-control" id="package">
                                            <option value="">Any</option>
                                            <option value="group">Group</option>
                                            <option value="individual">Individual</option>

                                           </select>

      									</div>


      								   <label class="col-md-2 control-label"></label>
      									 <div class="col-md-3">

                                          <button class="btn btn-primary btn-block advsearch">Search</button>


      								 </div>


      								 </div>
      								 </div>


      </div>
    </div>
  </div>

  <hr>   <?php } ?>
     <div id="ajax-data">


        <div  class="dataTables_wrapper form-inline" role="grid">


          <div id='rotatingDiv' class="" style="display:none"></div>
          <table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped table-bordered dataTable" >
            <thead>
              <tr role="row">
                <th><i class="fa fa-list-ol" data-toggle="tooltip" data-placement="top" title="Tour ID">&nbsp;</i></th>
                <th class="width25"><input class="pointer" type="checkbox" data-toggle="tooltip" data-placement="top"  title="Select All" id="select_all" /></th>
                <th class="width25"><i class="fa fa-laptop" data-toggle="tooltip" data-placement="top" title="Featured">&nbsp;</i></th>
                <th><span class="fa fa-picture-o" data-toggle="tooltip" data-placement="top" title="Image"></span> </th>
                <th>Name</th>
                <th>Category </th>
                <th>Date</th>
                <th>Owned by</th>
               <?php  if($adminsegment != "supplier"){ ?>  <th class="width25">Order </th> <?php } ?>
                <th><i class="fa fa-refresh" data-toggle="tooltip" data-placement="top" title="Status">&nbsp;</i></th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody role="alert" aria-live="polite" aria-relevant="all">

                         <?php

                  if(!empty($t_data)){
                  $count = 0;


            foreach($t_data['all'] as $wedding){
                  $count++;

                if($count % 2 == 0){
                  $evenodd = "even";
                }else{
                   $evenodd = "odd";
                }

               ?>

              <tr class="<?php echo $evenodd;?>">
                <td><?php echo $wedding->wedding_id;?></td>
                <td><input type="checkbox" name="wedding_ids[]" value="<?php echo $wedding->wedding_id;?>" class="selectedId"  /></td>
                <td>

                 <?php
                if($wedding->wedding_is_featured == '1'){
                if($adminsegment == "supplier"){ ?>
              <span class="fa fa-star featured-star" id="feat_<?php echo $wedding->wedding_id;?>" data-toggle="tooltip" data-placement="right" title="Featured <?php if(empty($wedding->wedding_featured_forever)){echo pt_show_date_php($wedding->wedding_featured_from)." to ".pt_show_date_php($wedding->wedding_featured_to);}else{ echo "Forever";} ?>"></span>
              <?php }else{ ?>
              <a class='inline' href="#Featured_Inline_<?php echo $wedding->wedding_id;?>" ><span class="fa fa-star featured-star" id="feat_<?php echo $wedding->wedding_id;?>" data-toggle="tooltip" data-placement="right" title="Featured <?php if(empty($wedding->wedding_featured_forever)){echo pt_show_date_php($wedding->wedding_featured_from)." to ".pt_show_date_php($wedding->wedding_featured_to);}else{ echo "Forever";} ?>"></span></a>
              <?php
                } }else{
                if($adminsegment == "supplier"){ ?>
              <span class="featured-star"><i class="fa fa-star-o" id="feat_<?php echo $wedding->wedding_id;?>" data-toggle="tooltip" data-placement="right" title="Not Featured"></i></span>
              <?php
                }else{
                 ?>
              <a class='inline' href="#Featured_Inline_<?php echo $wedding->wedding_id;?>"  ><span class="featured-star"><i class="fa fa-star-o" id="feat_<?php echo $wedding->wedding_id;?>" data-toggle="tooltip" data-placement="right" title="Not Featured"></i></span></a>
              <?php
                } }
                 ?>


                </td>
                <td class="zoom_img">
                  <?php
                  $defimg = pt_default_wedding_image($wedding->wedding_id);
                if(empty($defimg)){
                ?>
              <img src="<?php echo PT_DEFAULT_IMAGE.'wedding.png'; ?>" title="<?php echo $wedding->wedding_title;?>" />
              <?php
                }else{
                ?>
              <img src="<?php echo PT_TOURS_SLIDER_THUMB.$defimg; ?>" title="<?php echo $wedding->wedding_title;?>" />
              <?php
                }
                ?>
                </td>
                <td><a href="<?php echo base_url().$adminsegment; ?>/wedding/manage/<?php echo $wedding->wedding_slug;?>"><?php echo $wedding->wedding_title;?></a></td>
                <td><?php echo $wedding->sett_name;?></td>
                <td><?php echo pt_show_date_php($wedding->wedding_created_at); ?></td>
                <td><?php echo $wedding->ai_first_name." ".$wedding->ai_last_name;?></td>
              <?php  if($adminsegment != "supplier"){ ?>   <td>
                 <div class="form-group">
                <input class="form-control input-sm" type="number" id="order_<?php echo $wedding->wedding_id;?>" value="<?php echo $wedding->wedding_order;?>" min="1" max="<?php echo $max['nums'];?>" onblur="updateOrder($(this).val(),<?php echo $wedding->wedding_id;?>,<?php echo $wedding->wedding_order;?>);" />
              </div>
                </td> <?php } ?>
                <td>
                <?php

                if($wedding->wedding_status == '1'){
                    echo PT_ENABLED;
                }else{
                echo PT_DISABLED;

                }

                ?>
                </td>
                <td align="center">
                <span class="del_single" id="<?php echo $wedding->wedding_id;?>">  <?php echo PT_DEL; ?> </span>
                 <a href="<?php echo base_url().$adminsegment; ?>/wedding/manage/<?php echo $wedding->wedding_slug;?>"><?php echo PT_EDIT; ?></a>
                  <a href="<?php echo base_url().$adminsegment; ?>/wedding/translate/<?php echo $wedding->wedding_slug;?>"><span class="btn btn-xs btn-enable"><i class="fa fa-flag-checkered"></i> Translate</span></a>
             </tr>
             <?php }  } ?>


            </tbody>
          </table>

        <?php include 'application/modules/admin/views/includes/table-foot.php'; ?>

        </div>
      </div>
    </div>
    <!------Featured Box ---->
    <div style='display:none'>
      <?php
        foreach($t_data['all'] as $tfdata){
        ?>
      <div id="Featured_Inline_<?php echo $tfdata->wedding_id;?>" style='padding:30px; background:#fff;'>
        <form method="POST" action="<?php  echo  base_url(); ?>wedding/weddingajaxcalls/update_featured" class="form-horizontal my-form">
          <div class="form-group">
            <label class="col-md-3 control-label">Featured </label>
            <div class="col-md-3">
              <select data-placeholder="Yes" id="isfeatured_<?php echo $tfdata->wedding_id;?>" class="form-control" name="isfeatured" onchange="foreverOpt(this.options[this.selectedIndex].value,<?php echo $tfdata->wedding_id;?>)" >
                <option value="1" <?php if($tfdata->wedding_is_featured == "1"){echo "selected";}?> >Yes</option>
                <option value="0" <?php if($tfdata->wedding_is_featured == "0"){echo "selected";}?>  >No</option>
              </select>
            </div>

            <div class="form-group">
              <label class="col-md-1 control-label">From </label>
              <div class="col-md-3">
                <input class="form-control dpd1" type="text" id="dfrom_<?php echo $tfdata->wedding_id;?>" placeholder="From" value="<?php if(empty($tfdata->wedding_featured_forever)){ echo pt_show_date_php($tfdata->wedding_featured_from);}?>" name="ffrom" <?php if(!empty($tfdata->wedding_featured_forever)){echo "disabled";}?>>
              </div>
            </div>

          </div>

          <div class="form-group">
            <label class="col-md-3 control-label">When</label>
            <div class="col-md-3">
              <select data-placeholder="Forever" class="form-control" id="whendata_<?php echo $tfdata->wedding_id;?>" name="foreverfeatured" onchange="fdateOpt(this.options[this.selectedIndex].value,<?php echo $tfdata->wedding_id;?>)" <?php if($tfdata->wedding_is_featured == '0'){echo "disabled";}?>>
                <option value="forever" <?php if(!empty($tfdata->wedding_featured_forever)){echo "selected";}?> >Forever</option>
                <option value="bydate" <?php if(empty($tfdata->wedding_featured_forever)){echo "selected";}?> >By Date</option>
              </select>
            </div>
            <div class="form-group">
              <label class="col-md-1 control-label">To</label>
              <div class="col-md-3">
                <input class="form-control dpd2" id="dto_<?php echo $tfdata->wedding_id;?>" type="text" placeholder="To" value="<?php if(empty($tfdata->wedding_featured_forever)){echo pt_show_date_php($tfdata->wedding_featured_to);}?>" name="fto"  <?php if(!empty($tfdata->wedding_featured_forever)){echo "disabled";}?> >
              </div>
            </div>

          </div>
          <input type="hidden" name="weddingid" value="<?php echo $tfdata->wedding_id;?>" />
          <button type="submit" id="<?php echo $tfdata->wedding_id;?>" class="btn btn-primary btn-block btn-lg updatefeatured"> Done </button>
        </form>
      </div>
      <?php } ?>
    </div>
    <!------Featured Box ---->
  </div>
  <!------Featured Box ---->
</div>
