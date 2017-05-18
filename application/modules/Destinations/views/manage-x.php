<script type="text/javascript">
  $(function(){
    $("#image_default").change(function(){
      var preview_default = $('.default_preview_img');

   preview_default.fadeOut();

    /* html FileRender Api */
    var oFReader = new FileReader();
    oFReader.readAsDataURL(document.getElementById("image_default").files[0]);

    oFReader.onload = function (oFREvent) {
      preview_default.attr('src', oFREvent.target.result).fadeIn();

    };

  });
  })

</script>

<script type="text/javascript">
$(function(){
  $(".posttitle").blur(function(){
    var title = $(this).val();
    var postid = $("#postid").val();
    $.post("<?php echo base_url();?>admin/ajaxcalls/createDestinationsPermalink",{title: title, postid: postid},function(response){
        $(".permalink").val(response);
    });
  })
})
</script>
<div class="container">
  <form method="post" action="" enctype="multipart/form-data" >
  <?php $validationerrors = validation_errors();
       if(isset($errormsg) || !empty($validationerrors)){  ?>
    <div class="alert alert-danger">
      <i class="fa fa-times-circle"></i>
      <?php
        echo @$errormsg;
        echo $validationerrors; ?>
    </div>
    <?php  } ?>
    <div class="panel panel-default">

            <ul class="nav nav-tabs nav-justified" role="tablist">
            <li class="active"><a href="#GENERAL" data-toggle="tab"><?php echo ucfirst($action);?> Destination</a></li>
            <li class=""><a href="#TRANSLATE" data-toggle="tab">Translate</a></li>
        </ul>

      <div class="panel-body">
    <br>
            <div class="tab-content">
          <div class="tab-pane wow fadeIn animated active in" id="GENERAL">
          <div class="col-md-12">
            <div class="col-md-4">
              <div class="form-group ">
                <label class="required">Destination Title</label>
                <input class="form-control posttitle" type="text" placeholder="Destination Title" name="title" value="<?php echo  @$pdata[0]->destination_title;?>">
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-group ">
                <label class="required">Permalink : <?php echo base_url();?>/destinations/</label> <br>
                 <input class="form-control pull-right permalink" type="text" placeholder="Permalink" name="slug" value="<?php echo  @$pdata[0]->destination_slug;?>">
              </div>
            </div>



            <div class="col-md-12">
            <?php $this->ckeditor->editor('desc', @$pdata[0]->destination_desc, $ckconfig,'desc'); ?>
            </div>
          </div>
          <div class="clearfix"></div>
          <hr>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">Destination Settings</div>
                <div class="panel-body">

                <div class="col-md-4">
                    <div class="form-group ">
                      <label class="required">Latitude</label>
                      <input class="form-control posttitle" type="text" placeholder="latitude" name="title" value="<?php echo  @$pdata[0]->latitude;?>">
                    </div>

                    <div class="form-group ">
                      <label class="required">Longitude</label>
                      <input class="form-control posttitle" type="text" placeholder="longitude" name="title" value="<?php echo  @$pdata[0]->longitude;?>">
                    </div>

              <div class="form-group ">
                <label class="required">Status</label>
                <select data-placeholder="Select" name="status" class="form-control" tabindex="2">
                        <option value="Yes" <?php if(@$pdata[0]->destination_status == "Yes"){ echo "selected";} ?> >Enable</option>
                        <option value="No" <?php if(@$pdata[0]->destination_status == "No"){ echo "selected";} ?>>Disable</option>
                      </select>
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-group ">
                <label class="required">Thumbnail</label>
                <input style="width:220px" type="file" name="defaultphoto" class="btn btn-default" id="image_default" >

                 <?php if(!empty($pdata[0]->destination_images)){ ?>
                                      <img src="<?php echo PT_DESTINATION_IMAGES.$pdata[0]->destination_images; ?>" class="img-rounded thumbnail img-responsive default_preview_img" />
                                       <?php   }else{  ?>
                                    <img src="<?php echo PT_BLANK; ?>" class="img-rounded thumbnail img-responsive default_preview_img" />
                                           <?php  } ?>

              </div>
            </div>

                </div>
              </div>
            </div>

          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">SEO</div>
                <div class="panel-body form-horizontal">
                  <div class="form-group">
                    <label for="form-input" class="col-sm-1 control-label">Keywords</label>
                    <div class="col-sm-11">
                      <input class="form-control" type="text" name="keywords" value="<?php echo @$pdata[0]->destination_meta_keywords; ?>" placeholder="Keywords">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="form-input" class="col-sm-1 control-label">Description</label>
                    <div class="col-sm-11">
                      <input class="form-control" type="text" name="metadesc" value="<?php echo @$pdata[0]->destination_meta_desc; ?>" placeholder="Description">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>

          <!----Translation Tab---->

           <div class="tab-pane wow fadeIn animated in" id="TRANSLATE">

                    <?php foreach($languages as $lang => $val){ if($lang != "en"){ @$trans = getBackDestinationsTranslation($lang,$pdata[0]->destination_id);  ?>
                    <div class="panel panel-default">
                        <div class="panel-heading"><img src="<?php echo PT_LANGUAGE_IMAGES.$lang.".png"?>" height="20" alt="" /> <?php echo $val['name']; ?></div>
                        <div class="panel-body">
                            <div class="row form-group">
                                <label class="col-md-2 control-label text-left">Destination Title</label>
                                <div class="col-md-4">
                                    <input name='<?php echo "translated[$lang][title]"; ?>' type="text" placeholder="Destination Title" class="form-control" value="<?php echo @$trans[0]->trans_title;?>" />
                                </div>
                            </div>

                            <div class="row form-group">
                                <label class="col-md-2 control-label text-left">Destination Content</label>
                                <div class="col-md-10">
                                 <?php  $this->ckeditor->editor("translated[$lang][desc]", @$trans[0]->trans_desc, $ckconfig,"translated[$lang][desc]"); ?>

                                </div>
                            </div>

                            <hr>


                            <div class="row form-group">
                                <label class="col-md-2 control-label text-left">Meta Keywords</label>
                                <div class="col-md-6">
                                    <textarea name='<?php echo "translated[$lang][keywords]"; ?>' placeholder="Keywords" class="form-control" id="" cols="30" rows="2"><?php echo @$trans[0]->trans_keywords;?></textarea>
                                </div>
                            </div>

                            <div class="row form-group">
                                <label class="col-md-2 control-label text-left">Meta Description</label>
                                <div class="col-md-6">
                                    <textarea name='<?php echo "translated[$lang][metadesc]"; ?>' placeholder="Description" class="form-control" id="" cols="30" rows="4"><?php echo @$trans[0]->trans_meta_desc;?></textarea>
                                </div>
                            </div>


                        </div>
                    </div>
                    <?php } } ?>

                </div>

        </div>
      </div>
      <div class="panel-footer">
      <input type="hidden" name="action" value="<?php echo $action;?>" />
      <input type="hidden" id="postid" name="postid" value="<?php echo @$pdata[0]->destination_id;?>" />
      <input type="hidden" name="defimg" value="<?php echo @$pdata[0]->destination_images; ?>" />

        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
    </div>
  </form>
</div>
