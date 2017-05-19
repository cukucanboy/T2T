<script type="text/javascript">
  $(function(){
     var slug = $("#slug").val();
     $(".submitfrm").click(function(){
       var submitType = $(this).prop('id');
            for ( instance in CKEDITOR.instances )

      {

          CKEDITOR.instances[instance].updateElement();

      }
               $(".output").html("");
                $('html, body').animate({

                scrollTop: $('body').offset().top

                }, 'slow');
       if(submitType == "add"){
       url = "<?php echo base_url();?>admin/restaurant/add" ;

       }else{
       url = "<?php echo base_url();?>admin/restaurant/manage/"+slug;

       }

       $.post(url,$(".restaurant-form").serialize() , function(response){
          if($.trim(response) != "done"){
          $(".output").html(response);
          }else{
             window.location.href = "<?php echo base_url().$adminsegment."/restaurant/"?>";
          }

          });

     })



  })
</script>
<h3 class="margin-top-0"><?php //echo $tdata[0]->restaurant_title;?></h3>
<?php print_r(@$tobj); ?>
<div class="output"></div>
<form class="form-horizontal restaurant-form" method="POST" action="" enctype="multipart/form-data"  onsubmit="return false;" >
  <div class="panel panel-default">
    <ul class="nav nav-tabs nav-justified" role="tablist">
      <li class="active"><a href="#GENERAL" data-toggle="tab">General</a></li>
      <li class=""><a href="#INCLUSIONS" data-toggle="tab">Inclusions</a></li>
      <li class=""><a href="#EXCLUSIONS" data-toggle="tab">Exclusions</a></li>
      <li class=""><a href="#META_INFO" data-toggle="tab">Meta Info</a></li>
      <li class=""><a href="#POLICY" data-toggle="tab">Policy</a></li>
      <li class=""><a href="#CONTACT" data-toggle="tab">Contact</a></li>
      <li class=""><a href="#TRANSLATE" data-toggle="tab">Translate</a></li>
    </ul>
    <div class="panel-body">
      <br>
      <div class="tab-content form-horizontal">
        <div class="tab-pane wow fadeIn animated active in" id="GENERAL">
          <div class="clearfix"></div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Status</label>
            <div class="col-md-2">
              <select  class="form-control" name="restauranttatus">
                <option value="Yes" <?php if(@$tdata[0]->restaurant_status == "Yes"){echo "selected";} ?> >Enabled</option>
                <option value="No" <?php if(@$tdata[0]->restaurant_status == "No"){echo "selected";} ?> >Disabled</option>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Restaurant Name</label>
            <div class="col-md-4">
              <input class="form-control" type="text" placeholder="Restaurant Name" name="restaurantname" value="<?php echo @$tdata[0]->restaurant_title;?>" >
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Restaurant Description</label>
            <div class="col-md-10">
              <?php $this->ckeditor->editor('restaurantdesc', @$tdata[0]->restaurant_desc, $ckconfig,'restaurantdesc'); ?>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">&nbsp;</label>
            <div class="col-md-10">
              <table class="table table-striped table-bordered" cellspacing="1" bgcolor="#cccccc">
                <tbody>
                  <tr bgcolor="#efefef" style="text-align:center;font-weight:bold">
                    <td width="80"></td>
                    <td width="120">Adults</td>
                    <td width="90">Child</td>
                    <td width="100">Infant</td>
                  </tr>
                  <tr bgcolor="#ffffff" style="text-align:center">
                    <td>Quantity</td>
                    <td><input type="text" class="form-control input-sm adult" name="maxadult" <?php echo $adultStatus;?> id="" size="" value="<?php echo @$tdata[0]->restaurant_max_adults;?>"></td>
                    <td><input type="text" class="form-control input-sm child" name="maxchild" <?php echo $childStatus;?> id="" size="" value="<?php echo @$tdata[0]->restaurant_max_child;?>"></td>
                    <td><input type="text" class="form-control input-sm infant" name="maxinfant" <?php echo $infantStatus;?> id="" size="" value="<?php echo @$tdata[0]->restaurant_max_infant;?>"></td>
                  </tr>
                  <tr bgcolor="#ffffff" style="text-align:center">
                    <td>Price</td>
                    <td><input type="text" class="form-control input-sm adult" <?php echo $adultStatus;?> name="adultprice" id="" size="" value="<?php echo @$tdata[0]->restaurant_adult_price;?>"></td>
                    <td><input type="text" class="form-control input-sm child" <?php echo $childStatus;?> name="childprice" id="" size="" value="<?php echo @$tdata[0]->restaurant_child_price;?>"></td>
                    <td><input type="text" class="form-control input-sm infant" <?php echo $infantStatus;?> name="infantprice" id="" size="" value="<?php echo @$tdata[0]->restaurant_infant_price;?>"></td>
                  </tr>
                  <tr bgcolor="#ffffff" style="text-align:center">
                    <td>Enable</td>
                    <td>
                      <?php if(empty($adultStatus)){ ?>
                      <span class="btn btn-danger btn-xs disabledinput" id="adult"><span id="adultbtn" >Disable</span></span>
                      <?php }else{ ?>
                      <span class="btn btn-xs enabledinput btn-success" id="adult"><span id="adultbtn">Enable</span></span>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if(empty($childStatus)){ ?>
                      <span class="btn btn-xs disabledinput btn-danger" id="child"><span id="childbtn">Disable</span> </span>
                      <?php }else{ ?>
                      <span class="btn btn-success btn-xs enabledinput" id="child"><span id="childbtn" >Enable</span> </span>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if(empty($infantStatus)){ ?>
                      <span class="btn btn-xs disabledinput btn-danger" id="infant"><span id="infantbtn">Disable</span> </span>
                      <?php }else{ ?>
                      <span class="btn btn-success btn-xs enabledinput" id="infant"><span id="infantbtn" >Enable</span> </span>
                      <?php } ?>
                    </td>
                  </tr>
                </tbody>
              </table>
              <script type='text/javascript'>//<![CDATA[
                $(function () {
                    $("#infant").on("click",function(){
                        if($(this).hasClass("enabledinput")){
                          $(this).removeClass("enabledinput");
                        $(this).addClass("disabledinput");
                        $(this).addClass("btn-danger");
                        $(this).removeClass("btn-success");
                          $(".infant").prop("readonly",false);
                        $("#infantbtn").text("Disable");
                        $("#infantstatus").val("1");

                        }else{

                        $(this).removeClass("disabledinput");
                        $(this).addClass("enabledinput");
                        $(this).removeClass("btn-danger");
                        $(this).addClass("btn-success");
                          $(".infant").prop("readonly",true);
                        $("#infantbtn").text("Enable");
                        $("#infantstatus").val("0");


                        }

                    });

                     $("#child").on("click",function(){
                        if($(this).hasClass("enabledinput")){
                          $(this).removeClass("enabledinput");
                        $(this).addClass("disabledinput");
                        $(this).addClass("btn-danger");
                        $(this).removeClass("btn-success");
                          $(".child").prop("readonly",false);
                        $("#childbtn").text("Disable");
                        $("#childstatus").val("1");

                        }else{

                        $(this).removeClass("disabledinput");
                        $(this).addClass("enabledinput");
                        $(this).removeClass("btn-danger");
                        $(this).addClass("btn-success");
                          $(".child").prop("readonly",true);
                        $("#childbtn").text("Enable");
                        $("#childstatus").val("0");


                        }

                    })

                     $("#adult").on("click",function(){
                        if($(this).hasClass("enabledinput")){
                          $(this).removeClass("enabledinput");
                        $(this).addClass("disabledinput");
                        $(this).addClass("btn-danger");
                        $(this).removeClass("btn-success");
                          $(".adult").prop("readonly",false);
                        $("#adultbtn").text("Disable");
                        $("#adultstatus").val("1");
                        }else{

                        $(this).removeClass("disabledinput");
                        $(this).addClass("enabledinput");
                        $(this).removeClass("btn-danger");
                        $(this).addClass("btn-success");
                        $(".adult").prop("readonly",true);
                        $("#adultbtn").text("Enable");
                        $("#adultstatus").val("0");


                        }

                    })

                });
                //]]>

              </script>
            </div>
          </div>
          <div class="row form-group">
          <?php if($isadmin){ ?>
            <label class="col-md-2 control-label text-left">Stars</label>
            <div class="col-md-2">
              <select  class="form-control" name="restauranttars">
                <option value="0">Select</option>
                <?php
                  for($stars=0;$stars <=5;$stars++){?>
                <option value="<?php echo $stars;?>" <?php if($stars == @$tdata[0]->restaurant_stars ){echo "selected";} ?> ><?php echo $stars;?></option>
                <?php   } ?>
              </select>
            </div>
            <?php }else{ ?>
            <input type="hidden" name="restauranttars" value="<?php echo @$tdata[0]->restaurant_stars;?>">
            <?php } ?>
          </div>

          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Total Days</label>
            <div class="col-md-2">
              <input type="text"  class="form-control" name="restaurantdays" value="<?php echo @$tdata[0]->restaurant_days; ?>">
            </div>
          </div>

          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Total Nights</label>
            <div class="col-md-2">
              <input type="text"  class="form-control" name="restaurantnights" value="<?php echo @$tdata[0]->restaurant_nights; ?>">
            </div>
          </div>


          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Restaurant Type</label>
            <div class="col-md-4">
              <select  class="chosen-select" name="restauranttype">
                <option value="">Select</option>
                <?php if(!empty($restauranttypes)){
                  foreach($restauranttypes as $tt){ ?>
                <option value="<?php echo $tt->sett_id;?>" <?php if(@$tdata[0]->restaurant_type ==  $tt->sett_id ){echo "selected";} ?> ><?php echo $tt->sett_name;?></option>
                <?php  } } ?>
              </select>
            </div>
          </div>
          <div class="row form-group">
           <?php if($isadmin){ ?>
            <label class="col-md-2 control-label text-left">Featured</label>
            <div class="col-md-2">
              <select  Placeholder="No" class="form-control" Placeholder="No" name="isfeatured" id="isfeatured" onchange="changecollapse(this.options[this.selectedIndex].value,'Featured')">
                <option  value="no" <?php if(@$tdata[0]->restaurant_is_featured == "no"){echo "selected";} ?> >No</option>
                <option  value="yes" <?php if(@$tdata[0]->restaurant_is_featured == "yes"){echo "selected";} ?> >Yes</option>
              </select>
            </div>
            <div class="col-md-2">
              <input class="form-control dpd1" type="text" placeholder="From" value="<?php if(empty($tdata[0]->restaurant_featured_forever)){ echo @pt_show_date_php($tdata[0]->restaurant_featured_from);}?>" name="ffrom" >
            </div>
            <div class="col-md-2">
              <input class="form-control dpd2" type="text" placeholder="To" value="<?php if(empty($tdata[0]->restaurant_featured_forever)){ echo @pt_show_date_php($tdata[0]->restaurant_featured_to);}?>" name="fto" >
            </div>
          <?php  }else{ ?>
          <input type="hidden" name="isfeatured" value="<?php echo @$tdata[0]->restaurant_is_featured; ?>">
          <input type="hidden" name="ffrom" value="<?php if(empty($tdata[0]->restaurant_featured_forever)){ echo @pt_show_date_php($tdata[0]->restaurant_featured_from);}?>">
          <input type="hidden" name="fto" value="<?php if(empty($tdata[0]->restaurant_featured_forever)){ echo @pt_show_date_php($tdata[0]->restaurant_featured_to);}?>">
          <?php } ?>

          </div>


          <div class="panel panel-default">
            <div class="panel-heading">Locations</div>
            <div class="panel-body">

            <?php  for($i=1; $i<=3; $i++) { $locationName =  @$restaurantlocations[$i]->name; ?>

            <label class="col-md-2 control-label text-left">Location <?php echo $i; ?></label>
            <div class="col-md-6">

              <input type="text" id="locationlist<?php echo $i; ?>">
              <input type="hidden" name="locations[]" id="locationid<?php echo $i;?>" required="" value="<?php echo @$restaurantlocations[$i]->id; ?>">


            </div>
            <div class="clearfix"></div>
            <br>
            <script>
            $(function(){

                    $("#locationlist<?php echo $i;?>").select2(
                      {
                              placeholder: "<?php if(empty($locationName)){ echo "Enter Location"; }else{ echo @$locationName; } ?>",
                              minimumInputLength: 3,
                              width:'100%', maximumSelectionSize: 1,
                              initSelection: function (element, callback) {
                                      var data = {id: "1", text: "<?php echo @$locationName; ?>"};
                                      callback(data);
                                  },
                              ajax: {
                                  url: "<?php echo base_url(); ?>admin/ajaxcalls/locationsList",
                                  dataType: 'json',
                                  data: function (term, page) {
                                      return {
                                          query: term, // search term

                                      };
                                  },
                                  results: function (data, page) {

                                      return {results: data};
                                  }
                              }
                          }
                     );

                      $("#locationlist<?php echo $i;?>").on("select2-selecting", function(e) {
                       $("#locationid<?php echo $i;?>").val(e.val);
                       console.log(e.val);
                    });
            })
            </script>
             <?php } ?>

            </div>
          </div>

          <div class="row form-group">

          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left text-success">Deposit / Commission</label>
            <div class="col-md-2">
            <?php  if($isadmin){ ?>
              <select name="deposittype" class="form-control">
                <option value="fixed" <?php if(@$restaurantdeposittype == "fixed"){ echo "selected";} ?> >Fixed</option>
                <option value="percentage" <?php if(@$restaurantdeposittype == "percentage"){ echo "selected";} ?> >Percentage</option>
              </select>
               <?php }else{ ?><input type="text" class="form-control" name="deposittype" value="<?php echo $restaurantdeposittype; ?>" readonly="readonly"><?php } ?>

            </div>
            <div class="col-md-2">
              <input type="text" class="form-control" id="" placeholder="" name="depositvalue" value="<?php echo @$restaurantdepositval; ?>" <?php if(!$isadmin){ echo "readonly"; } ?>  >
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left text-danger">Vat Tax</label>
            <div class="col-md-2">
              <select name="taxtype" class="form-control">
                <option value="fixed" <?php if(@$restauranttaxtype == "fixed"){ echo "selected";} ?> >Fixed</option>
                <option value="percentage" <?php if(@$restauranttaxtype == "percentage"){ echo "selected";} ?> >Percentage</option>
              </select>
            </div>
            <div class="col-md-2">
              <input class="form-control" id="" Placeholder="" type="text" name="taxvalue" value="<?php echo @$restauranttaxval; ?>"  />
            </div>
          </div>
          <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
            <label class="col-md-2 control-label text-left">Related restaurant</label>
            <div class="col-md-8">
              <select multiple class="chosen-multi-select" name="relatedrestaurant[]">
                <?php if(!empty($all_restaurant)){ $restaurantrelated = explode(",",$tdata[0]->restaurant_related);
                  foreach($all_restaurant as $t):
                  ?>
                <option value="<?php echo $t->restaurant_id;?>" <?php if(in_array($t->restaurant_id,$restaurantrelated)){echo "selected";} ?>  ><?php echo $t->restaurant_title;?></option>
                <?php endforeach; } ?>
              </select>
            </div>
          </div>


          <!--Related Products-->
                  <div class="panel panel-default">
                        <div class="panel-heading">Related Products</div>
                        <div class="panel-body">

                    <!--Related Activity -->
                      <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
                        <label class="col-md-2 control-label text-left">Related Activity</label>
                        <div class="col-md-8">
                          <select multiple class="chosen-multi-select" name="relatedProdActivity[]">
                            <?php if(!empty($all_activity)){ $activityRelated = explode(",",$tdata[0]->product_related_activity);
                              foreach($all_activity as $t):
                              ?>
                            <option value="<?php echo $t->activity_id;?>" <?php if(in_array($t->activity_id,$activityRelated)){echo "selected";} ?>  ><?php echo $t->activity_title;?></option>
                            <?php endforeach; } ?>
                          </select>
                        </div>
                      </div>

                      <!--Related Hotels -->
                        <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
                          <label class="col-md-2 control-label text-left">Related Hotels</label>
                          <div class="col-md-8">
                            <select multiple class="chosen-multi-select" name="relatedProdHotels[]">
                              <?php if(!empty($all_hotels)){ $hotelsRelated = explode(",",$tdata[0]->product_related_hotels);
                                foreach($all_hotels as $t):
                                ?>
                              <option value="<?php echo $t->hotel_id;?>" <?php if(in_array($t->hotel_id,$hotelsRelated)){echo "selected";} ?>  ><?php echo $t->hotel_title;?></option>
                              <?php endforeach; } ?>
                            </select>
                          </div>
                        </div>

                    <!--Related Wedding -->
                      <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
                        <label class="col-md-2 control-label text-left">Related Wedding</label>
                        <div class="col-md-8">
                          <select multiple class="chosen-multi-select" name="relatedProdWedding[]">
                            <?php if(!empty($all_wedding)){ $weddingRelated = explode(",",$tdata[0]->product_related_wedding);
                              foreach($all_wedding as $t):
                              ?>
                            <option value="<?php echo $t->wedding_id;?>" <?php if(in_array($t->wedding_id,$weddingRelated)){echo "selected";} ?>  ><?php echo $t->wedding_title;?></option>
                            <?php endforeach; } ?>
                          </select>
                        </div>
                      </div>

                    <!--Related Tour -->
                      <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
                        <label class="col-md-2 control-label text-left">Related tour</label>
                        <div class="col-md-8">
                          <select multiple class="chosen-multi-select" name="relatedProdTours[]">
                            <?php if(!empty($all_tours)){$toursRelated = explode(",",$tdata[0]->product_related_tours);
                              foreach($all_tours as $t):
                              ?>
                            <option value="<?php echo $t->tour_id;?>" <?php if(in_array($t->tour_id,$toursRelated)){echo "selected";} ?>  ><?php echo $t->tour_title;?></option>
                            <?php endforeach; } ?>
                          </select>
                        </div>
                      </div>

                    <!--Related Spa -->
                      <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
                        <label class="col-md-2 control-label text-left">Related Spa</label>
                        <div class="col-md-8">
                          <select multiple class="chosen-multi-select" name="relatedProdSpa[]">
                            <?php if(!empty($all_spa)){ $spaRelated = explode(",",$tdata[0]->product_related_spa);
                              foreach($all_spa as $t):
                              ?>
                            <option value="<?php echo $t->spa_id;?>" <?php if(in_array($t->spa_id,$spaRelated)){echo "selected";} ?>  ><?php echo $t->spa_title;?></option>
                            <?php endforeach; } ?>
                          </select>
                        </div>
                      </div>

                    <!--Related Car -->
                      <div class="row form-group" style='<?php if($adminsegment == "supplier"){ echo "display:none;"; } ?>'>
                        <label class="col-md-2 control-label text-left">Related Car</label>
                        <div class="col-md-8">
                          <select multiple class="chosen-multi-select" name="relatedProdCars[]">
                            <?php if(!empty($all_cars)){ $carsRelated = explode(",",$tdata[0]->product_related_cars);
                              foreach($all_cars as $t):
                              ?>
                            <option value="<?php echo $t->car_id;?>" <?php if(in_array($t->car_id,$carsRelated)){echo "selected";} ?>  ><?php echo $t->car_title;?></option>
                            <?php endforeach; } ?>
                          </select>
                        </div>
                      </div>
                        </div>
                      </div>
          <!--Related Products-->



         <!-- Address and Map -->
        <div class="panel panel-default">
        <div class="panel-heading"><strong>Map Address for Listing Page</strong></div>
        <div class="well well-sm" style="margin-bottom: 0px;">
        <div class="col-md-6 form-horizontal">
        <table class="table">
        <tr>
        <td>Address on Map</td>
        <td>
        <input type="text" class="form-control Places" id="mapaddress" name="restaurantmapaddress" value="<?php echo @$tdata[0]->restaurant_mapaddress;?>">
        </td>
        </tr>
        <tr>
        <td></td>
        </tr>
        <tr>
        <td>Latitude</td>
        <td><input type="text" class="form-control" id="latitude" value="<?php echo @$tdata[0]->restaurant_latitude;?>"  name="latitude" /></td>
        </tr>
        <tr>
        <td>Longitude</td>
        <td><input type="text" class="form-control" id="longitude" value="<?php echo @$tdata[0]->restaurant_longitude;?>"  name="longitude" /></td>
        </tr>
        </table>

        </div>
        <div class="col-md-6">
        <div class="thumbnail">
        <div id="map-canvas" style="height: 200px; width:400"></div>
        </div>
        </div>
        <div class="clearfix"></div>
        </div>
        </div>
          <!-- Address and Map -->

        </div>
        <div class="tab-pane wow fadeIn animated in" id="INCLUSIONS">
          <div class="row form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label class="pointer"><input class="all" type="checkbox" name="" value="" id="select_all" > Select All</label>
              </div>
              <div class="clearfix"></div>
              <hr>
              <div class="clearfix"></div>
              <?php
              if(!empty($tdata[0]->restaurant_amenities)){
                      $inclusions = explode(",",$tdata[0]->restaurant_amenities);
              }


                foreach($restaurantinclusions as $ti){ ?>
              <div class="col-md-4">
                <label class="pointer"><input class="checkboxcls" <?php if($submittype == "add"){ if( $ti->sett_selected == "Yes"){echo "checked";} }else{ if(in_array($ti->sett_id,$inclusions)){ echo "checked"; } } ?> type="checkbox" name="restaurantamenities[]" value="<?php echo $ti->sett_id;?>"  > <?php echo $ti->sett_name;?></label>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="tab-pane wow fadeIn animated in" id="EXCLUSIONS">
          <div class="row form-group">
            <div class="col-md-12">
              <div class="col-md-4">
                <label class="pointer"><input class="all" type="checkbox" name="" value="" id="select_all" > Select All</label>
              </div>
              <div class="clearfix"></div>
              <hr>
              <div class="clearfix"></div>
              <?php
if(!empty($tdata[0]->restaurant_exclusions)){
               $exclusions = explode(",",$tdata[0]->restaurant_exclusions);
}
                foreach($restaurantexclusions as $te){ ?>
              <div class="col-md-4">
                <label class="pointer"><input class="checkboxcls" <?php if($submittype == "add"){ if( $te->sett_selected == "Yes"){echo "checked";} }else{ if(in_array($te->sett_id,$exclusions)){ echo "checked"; } } ?> type="checkbox" name="restaurantexclusions[]" value="<?php echo $te->sett_id;?>"  > <?php echo $te->sett_name;?></label>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="tab-pane wow fadeIn animated in" id="META_INFO">
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Meta Title</label>
            <div class="col-md-6">
              <input class="form-control" type="text" placeholder="Meta title" name="restaurantmetatitle"  value="<?php echo @$tdata[0]->restaurant_meta_title;?>">
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Meta Keywords</label>
            <div class="col-md-6">
              <input class="form-control" type="text" placeholder="Meta keywords" name="restaurantkeywords"  value="<?php echo @$tdata[0]->restaurant_meta_keywords;?>">
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Meta Description</label>
            <div class="col-md-6">
              <textarea class="form-control" placeholder="Meta description here..." name="restaurantmetadesc" rows="5"><?php echo @$tdata[0]->restaurant_meta_desc;?></textarea>
            </div>
          </div>
        </div>
        <div class="tab-pane wow fadeIn animated in" id="POLICY">
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Payment Options</label>
            <div class="col-md-6">
              <select multiple class="chosen-multi-select" name="restaurantpayments[]">
                <?php if(!empty($restaurantpayments)){ $tpayments = explode(",",$tdata[0]->restaurant_payment_opt); foreach($restaurantpayments as $tp){ ?>
                <option value="<?php echo $tp->sett_id;?>"  <?php if($submittype == "add"){ if( $tip->sett_selected == "Yes"){echo "selected";} }else{ if(in_array($tp->sett_id,$tpayments)){ echo "selected"; } } ?> ><?php echo $tp->sett_name;?></option>
                <?php  } } ?>
              </select>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Policy And Terms</label>
            <div class="col-md-8">
              <textarea class="form-control" placeholder="Privacy Policy..." name="restaurantprivacy" rows="3"><?php echo @$tdata[0]->restaurant_privacy;?> </textarea>
            </div>
          </div>
        </div>
        <div class="tab-pane wow fadeIn animated in" id="CONTACT">
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Restaurant Operator's Email</label>
            <div class="col-md-4">
              <input class="form-control" type="email" placeholder="Restaurant's Email" name="restaurantemail"  value="<?php echo @$tdata[0]->restaurant_email;?>" >
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Operator's Website</label>
            <div class="col-md-4">
              <input name="restaurantwebsite" type="text" placeholder="Website" class="form-control " value="<?php echo @$tdata[0]->restaurant_website;?>" />
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Operator's Phone</label>
            <div class="col-md-4">
              <input name="restaurantphone" type="text" placeholder="Phone" class="form-control" value="<?php echo @$tdata[0]->restaurant_phone;?>" />
            </div>
          </div>
          <div class="row form-group">
            <label class="col-md-2 control-label text-left">Operator's Full Address</label>
            <div class="col-md-6">
              <input name="restaurantfulladdress" type="text" placeholder="Address" class="form-control" value="<?php echo @$tdata[0]->restaurant_fulladdress;?>" />
            </div>
          </div>
        </div>
        <div class="tab-pane wow fadeIn animated in" id="TRANSLATE">
          <?php foreach($languages as $lang => $val){ if($lang != "en"){ @$trans = getBackRestaurantTranslation($lang,$restaurantid); ?>
          <div class="panel panel-default">
            <div class="panel-heading"><img src="<?php echo PT_LANGUAGE_IMAGES.$lang.".png"?>" height="20" alt="" /> <?php echo $val['name']; ?></div>
            <div class="panel-body">
              <div class="row form-group">
                <label class="col-md-2 control-label text-left">Restaurant Name</label>
                <div class="col-md-4">
                  <input name='<?php echo "translated[$lang][title]"; ?>' type="text" placeholder="Restaurant Name" class="form-control" value="<?php echo @$trans[0]->trans_title;?>" />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-md-2 control-label text-left">Restaurant Description</label>
                <div class="col-md-10">
                  <?php $this->ckeditor->editor("translated[$lang][desc]", @$trans[0]->trans_desc, $ckconfig,"translated[$lang][desc]"); ?>
                  <!--    <textarea name='<?php echo "translated[$lang][desc]"; ?>' placeholder="Description..." class="form-control" id="" cols="30" rows="4"><?php echo @$trans[0]->trans_desc;?></textarea>   -->
                </div>
              </div>
              <hr>
              <div class="row form-group">
                <label class="col-md-2 control-label text-left">Meta Title</label>
                <div class="col-md-6">
                  <input name='<?php echo "translated[$lang][metatitle]"; ?>' type="text" placeholder="Title" class="form-control" value="<?php echo @$trans[0]->metatitle;?>" />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-md-2 control-label text-left">Meta Keywords</label>
                <div class="col-md-6">
                  <textarea name='<?php echo "translated[$lang][keywords]"; ?>' placeholder="Keywords" class="form-control" id="" cols="30" rows="2"><?php echo @$trans[0]->metakeywords;?></textarea>
                </div>
              </div>
              <div class="row form-group">
                <label class="col-md-2 control-label text-left">Meta Description</label>
                <div class="col-md-6">
                  <textarea name='<?php echo "translated[$lang][metadesc]"; ?>' placeholder="Description" class="form-control" id="" cols="30" rows="4"><?php echo @$trans[0]->metadesc;?></textarea>
                </div>
              </div>
              <hr>
              <div class="row form-group">
                <label class="col-md-2 control-label text-left">Policy And Terms</label>
                <div class="col-md-8">
                  <textarea name='<?php echo "translated[$lang][policy]"; ?>' placeholder="Policy..." class="form-control" id="" cols="15" rows="4"><?php echo @$trans[0]->trans_policy;?></textarea>
                </div>
              </div>
            </div>
          </div>
          <?php } } ?>
        </div>
      </div>
    </div>
    <div class="panel-footer">
      <input type="hidden" id="slug" value="<?php echo @$tdata[0]->restaurant_slug;?>" />
      <input type="hidden" name="submittype" value="<?php echo $submittype;?>" />
      <input type="hidden" name="restaurantid" value="<?php echo @$restaurantid;?>" />
      <input type="hidden" name="adultstatus" id="adultstatus" value="<?php echo $adultInput;?>" />
      <input type="hidden" name="childstatus" id="childstatus" value="<?php echo $childInput;?>" />
      <input type="hidden" name="infantstatus" id="infantstatus" value="<?php echo $infantInput;?>" />
      <button class="btn btn-primary submitfrm" id="<?php echo $submittype; ?>"> Submit </button>
    </div>
  </div>
</form>


<!-- google places -->

    <script>

      function initAutocomplete() {

        var markers = [];

        var ex_latitude = $('#latitude').val();

        var ex_longitude = $('#longitude').val();

          if (ex_latitude != '' && ex_longitude != ''){

            var map = new google.maps.Map(document.getElementById('map-canvas'), {
          center: {lat: parseFloat(ex_latitude), lng: parseFloat(ex_longitude)},
          zoom: 16,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        });


             var marker = new google.maps.Marker(

                {

                    map: map,

                    draggable:true,
                    icon: "<?php echo PT_DEFAULT_IMAGE . 'marker.png'; ?>",

                    animation: google.maps.Animation.DROP,

                    position: new google.maps.LatLng(ex_latitude, ex_longitude)

                });



            markers.push(marker);

            google.maps.event.addListener(marker, 'dragend', function()

            {

                var marker_positions = marker.getPosition();

                $('#latitude').val(marker_positions.lat());

                $('#longitude').val(marker_positions.lng());



            });


          }else{

            var map = new google.maps.Map(document.getElementById('map-canvas'), {
          center: {lat: -33.8688, lng: 151.2195},
          zoom: 16,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        });

          }



        // Create the search box and link it to the UI element.
        var input = document.getElementById('mapaddress');
        var searchBox = new google.maps.places.SearchBox(input);
       // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());

        });

        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();
          if (places.length == 0) {
            return;
          }


map.setZoom(16);

          // Clear out the old markers.
          markers.forEach(function(marker) {
            marker.setMap(null);


          });
          markers = [];

          // For each place, get the icon, name and location.
          var bounds = new google.maps.LatLngBounds();
          places.forEach(function(place) {
            var icon = {
              url: place.icon,
              size: new google.maps.Size(71, 71),
              origin: new google.maps.Point(0, 0),
              anchor: new google.maps.Point(17, 34),
              scaledSize: new google.maps.Size(25, 25)
            };

            var marker = new google.maps.Marker({
              map: map,
              icon: "<?php echo PT_DEFAULT_IMAGE . 'marker.png'; ?>",
              title: place.name,
              position: place.geometry.location,
              draggable: true,
              animation: google.maps.Animation.DROP,
            });
            // Create a marker for each place.
            markers.push(marker);

            if (place.geometry.viewport) {
              // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }

          google.maps.event.addListener(marker, 'dragend', function()

            {

                var marker_positions = marker.getPosition();

                $('#latitude').val(marker_positions.lat());

                $('#longitude').val(marker_positions.lng());



            });



      $('#latitude').val(place.geometry.location.lat());
      $('#longitude').val(place.geometry.location.lng());


          });



          map.fitBounds(bounds);
          map.setZoom(16);

        });




      }


    </script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $appSettings->mapApi; ?>&libraries=places&callback=initAutocomplete"
         async defer></script>
 <!-- Google Places -->

<script>
  $(document).ready(function() {
      if (window.location.hash != "") {
          $('a[href="' + window.location.hash + '"]').click()
      }


  });
</script>
