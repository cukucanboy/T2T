<?php if(!empty($module->amenities)){ ?>
<div class="panel panel-default">
    <div class="panel-heading go-text-right panel-blue"><?php echo trans('048');?></div>
    <div class="panel-body">
        <div class="go-text-right">
            <?php foreach($module->amenities as $amt){ if(!empty($amt->name)){ ?>
            <div style="margin-top:6px;margin-left:0px" class="col-xs-6 col-md-4 col-sm-4">
                <div class="row">
                    <img class="go-right" style="max-height:30px;max-witdh:40px" src="<?php echo $amt->icon;?>">
                    <span class="text-left go-text-right size14">
                    <?php echo $amt->name; ?>
                    </span>
                </div>
            </div>
            <?php } } ?>
        </div>
    </div>
</div>
<?php } ?>
