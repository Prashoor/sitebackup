<! DOCTYPE html>
<html>

<head>
    <script src="<?php echo base_url('assets/js/jquery-3.1.0.min.js')?>" type="text/javascript"></script>
    <script src="<?php echo base_url('assets/js/jquery.form.min.js')?>" type="text/javascript"></script>
    <script src="<?php echo base_url('assets/js/jquery.dataTables.min.js')?>" type="text/javascript"></script>
    <script src="<?php echo base_url('assets/js/script.js')?>" type="text/javascript"></script>
    <script src="<?php echo base_url('assets/videojs/video.min.js')?>" type="text/javascript"></script>
    <link rel="icon" href="<?=base_url()?>assets/favicon.jpg" type="image/jpg">
    <script src="<?php echo base_url('assets/js/app.js')?>" type="text/javascript"></script>

    <?php echo link_tag('assets/css/jquery.dataTables.min.css'); ?>
    <?php echo link_tag('assets/videojs/video-js.min.css'); ?>
    <?php echo link_tag('assets/font-awesome/css/font-awesome.min.css'); ?>
    <?php echo link_tag('assets/css/uploader.css'); ?>
    <?php echo link_tag('assets/css/index.css'); ?>
</head>

<body>
    <div class="header">
        <div class="title">
            VideoManager
        </div>
        <div class="navs">
            <ul class="nav">
                <li><a href="#videolist" class="selected">All Videos</a></li>
                <li><a href="#uploadpage">Edit Videos</a></li>
                <li><a href="<?php echo site_url('admin/logout');?>">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="container show" id="videolist">
        <div class="header">
            <h3>
                All Videos
            </h3>
            <button>
                <i class="fa fa-refresh"></i>&nbsp;Reload data
            </button>
            <button class="upload-video">
                <i class="fa fa-upload" aria-hidden="true"></i> Upload Video
            </button>
        </div>
        <div class="video-list">
            <?php if (count($allvideos) == 0) { ?>
            <div style="font-family: Vegur; margin: 15% auto auto 15%">
                No Videos to show
            </div>
            <?php } ?>
            <?php foreach ($allvideos as $cat => $videos) { ?>
            <?php if (count($videos) > 0) {?>
            <div class="titlecategory">
                <h3>
                            <?php echo $cat;?>
                        </h3>
            </div>
            <div class="videolist">

                <?php foreach ($videos as $video) { ?>
                <div class="videocontainer" data-video="<?php echo $video['video']; ?>" data-image="<?php echo $video['image_name'];?>">
                    <div class="videotitle">
                        <?php echo $video['video_name']; ?>
                        <span class="cost"> <?php echo $video['cost']; ?></span>
                    </div>
                    <div class="imagedisp" style="background: rgb(0,0,0) url('<?php echo $video['image_name'];?>');">
                        <div></div>
                    </div>
                    <div class="aboutvideo">
                        <p class="video-description">
                            <?php echo $video['description']; ?>
                        </p>
                    </div>
                </div>
                <?php } ?>
            </div>

            <?php  }
              }
            ?>
        </div>
    </div>

    <div class="container" id="uploadpage">
        <div class="header">
            <h3>
                Edit Videos
            </h3>
            <button>
                <i class="fa fa-refresh"></i>&nbsp;Reload data
            </button>
            <button class="upload-video">
                <i class="fa fa-upload" aria-hidden="true"></i> Upload Video
            </button>
        </div>
        <div>
            <table id="videotable" class="row-border hover">
                <thead>
                    <tr>
                        <th>S. No.</th>
                        <th>Video Name</th>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Category</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div style="display: none" class="uploadform modalback">
        <div class="uploadview">

            <?php
      $attributes = array('name' => 'video_upload_form', 'id' => 'video_upload_form');
      echo form_open_multipart('videoupload/upload_video', $attributes);
    ?>
                <input type="hidden" value="videoForm" name="<?php echo ini_get(" session.upload_progress.name "); ?>">
                <ul class="form">
                    <li><label class="input"><span>Video Name </span><input name="videoname" type="text" placeholder="Enter Video Name"/></label></li>
                    <li><label class="input"><span>Video Description </span><textarea name="videodesc" placeholder="Enter Video Description"></textarea></label></li>
                    <li><label class="fileupload" style="cursor: pointer;"><input name="video" type="file" />Select Video</label></li>
                    <li><label class="input">
                        <span>Price </span>
                        <select name="cost" type="text" placeholder="Select Cost">
                            <option value="free">Free</option>
                            <option value="paid">Paid</option>
                        </select>
                        </label></li>
                    <li><label class="input">
                        <span>Category </span>
                        <select name="category" type="text" placeholder="Select Category">
                            <?php foreach($categories as $cat) {?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name'];?></option>
                                <?php }?>
                        </select>
                        </label>
                    </li>
                    <li><input name="video_upload" value="Upload Video" type="submit" /><button type="button" class="closeuploader">Close</button></li>
                </ul>
                <?php
      echo form_close();
    ?>
                    <div class="popup popup-loader">
                        <div class="popup-content">
                            <div class="message">
                                <span>Uploading Your File<br/>Please Wait...</span>
                            </div>
                            <div id="video-uploader" class="loader">
                                <span style="width: 3%;"></span>
                            </div>
                            <h2 style="color: #36e758">Uploaded 0%</h2>
                        </div>
                    </div>
        </div>
    </div>
    <div style="display: none" class="editform modalback">
        <div class="uploadview">
            <?php
      $attributes = array('name' => 'video_edit_form', 'id' => 'video_edit_form');
      echo form_open('videoupload/editvideo', $attributes);
    ?>
            <input type="hidden" name="videoid"/>
                <ul class="form">
                    <li><label class="input"><span>Video Name </span><input name="videoname" type="text" placeholder="Enter Video Name"/></label></li>
                    <li><label class="input"><span>Video Description </span><textarea name="videodesc" placeholder="Enter Video Description"></textarea></label></li>
                    <li><label class="input">
                        <span>Price </span>
                        <select name="cost" type="text" placeholder="Select Cost">
                            <option value="free">Free</option>
                            <option value="paid">Paid</option>
                        </select>
                        </label></li>
                    <li><label class="input">
                        <span>Category </span>
                        <select name="category" type="text" placeholder="Select Category">
                            <?php foreach($categories as $cat) {?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name'];?></option>
                                <?php }?>
                        </select>
                        </label>
                    </li>
                    <li><input name="edit_submit" value="Submit Changes" type="submit" /><button type="button" class="closeuploader">Close</button></li>
                </ul>
                <?php
      echo form_close();
    ?>
        </div>
    </div>
</body>

</html>