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
    var wishid = $("#wishid").val();
    $.post("<?php echo base_url();?>admin/ajaxcalls/createWishPermalink",{title: title, wishid: wishid},function(response){
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
            <li class="active"><a href="#GENERAL" data-toggle="tab"><?php echo ucfirst($action);?> Wish</a></li>
            <li class=""><a href="#TRANSLATE" data-toggle="tab">Translate</a></li>
        </ul>

      <div class="panel-body">
    <br>
            <div class="tab-content">
          <div class="tab-pane wow fadeIn animated active in" id="GENERAL">
          <div class="col-md-12">
            <div class="col-md-4">
              <div class="form-group ">
                <label class="required">Wish Title</label>
                <input class="form-control posttitle" type="text" placeholder="Wish Title" name="title" value="<?php echo  @$pdata[0]->wish_title;?>">
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-group ">
                <label class="required">Permalink : <?php echo base_url();?>wish/</label> <br>
                 <input class="form-control pull-right permalink" type="text" placeholder="Permalink" name="slug" value="<?php echo  @$pdata[0]->wish_slug;?>">
              </div>
            </div>



            <div class="col-md-12">
            <?php $this->ckeditor->editor('desc', @$pdata[0]->wish_desc, $ckconfig,'desc'); ?>
            </div>
          </div>
          <div class="clearfix"></div>
          <hr>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">Wish Settings</div>
                <div class="panel-body">

                <div class="col-md-6">

              <div class="form-group ">
                <label class="required">Status</label>
                <select data-placeholder="Select" name="status" class="form-control" tabindex="2">
                        <option value="Yes" <?php if(@$pdata[0]->wish_status == "Yes"){ echo "selected";} ?> >Enable</option>
                        <option value="No" <?php if(@$pdata[0]->wish_status == "No"){ echo "selected";} ?>>Disable</option>
                      </select>
              </div>
            </div>






            <div class="col-md-6">
              <div class="form-group ">
                <label class="required">Thumbnail</label>
                <input style="width:220px" type="file" name="defaultphoto" class="btn btn-default" id="image_default" >

                 <?php if(!empty($pdata[0]->wish_images)){ ?>
                                      <img src="<?php echo PT_WISH_IMAGES.$pdata[0]->wish_images; ?>" class="img-rounded thumbnail img-responsive default_preview_img" />
                                       <?php   }else{  ?>
                                    <img src="<?php echo PT_BLANK; ?>" class="img-rounded thumbnail img-responsive default_preview_img" />
                                           <?php  } ?>

              </div>
            </div>





                </div>
              </div>
            </div>
            .
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">SEO</div>
                <div class="panel-body form-horizontal">
                  <div class="form-group">
                    <label for="form-input" class="col-sm-1 control-label">Keywords</label>
                    <div class="col-sm-11">
                      <input class="form-control" type="text" name="keywords" value="<?php echo @$pdata[0]->wish_meta_keywords; ?>" placeholder="Keywords">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="form-input" class="col-sm-1 control-label">Wish</label>
                    <div class="col-sm-11">
                      <input class="form-control" type="text" name="metadesc" value="<?php echo @$pdata[0]->wish_meta_desc; ?>" placeholder="Wish">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>

          <!----Translation Tab---->

           <div class="tab-pane wow fadeIn animated in" id="TRANSLATE">

                    <?php foreach($languages as $lang => $val){ if($lang != "en"){ @$trans = getBackBlogTranslation($lang,$pdata[0]->post_id);  ?>
                    <div class="panel panel-default">
                        <div class="panel-heading"><img src="<?php echo PT_LANGUAGE_IMAGES.$lang.".png"?>" height="20" alt="" /> <?php echo $val['name']; ?></div>
                        <div class="panel-body">
                            <div class="row form-group">
                                <label class="col-md-2 control-label text-left">Wish Title</label>
                                <div class="col-md-4">
                                    <input name='<?php echo "translated[$lang][title]"; ?>' type="text" placeholder="Wish Title" class="form-control" value="<?php echo @$trans[0]->trans_title;?>" />
                                </div>
                            </div>

                            <div class="row form-group">
                                <label class="col-md-2 control-label text-left">Wish Content</label>
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
                                <label class="col-md-2 control-label text-left">Meta Wish</label>
                                <div class="col-md-6">
                                    <textarea name='<?php echo "translated[$lang][metadesc]"; ?>' placeholder="Wish" class="form-control" id="" cols="30" rows="4"><?php echo @$trans[0]->trans_meta_desc;?></textarea>
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
      <input type="hidden" id="wishid" name="wishid" value="<?php echo @$pdata[0]->wish_id;?>" />
      <input type="hidden" name="defimg" value="<?php echo @$pdata[0]->wish_images; ?>" />

        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
    </div>
  </form>
</div>
