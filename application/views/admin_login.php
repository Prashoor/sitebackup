<html>
<head>
<title>Admin Login</title>
    <link rel="icon" href="<?=base_url()?>assets/favicon.jpg" type="image/jpg">
    <?php echo link_tag('assets/css/admin_login.css'); ?>
</head>
<body>
<div class="centerform">
    <?php echo form_open('videoadmin'); ?>
    <h2>
        Admin Login
    </h2>
    <h5>Username</h5>
    <input type="text" name="uname" value="" placeholder="Enter UserName" size="50" />

    <h5>Password</h5>
    <input type="password" name="passwd" value="" placeholder="Enter Passsword" size="50" />

    <div><input type="submit" value="Submit" /></div>

    </form>
    <div class="error">
        <?php echo validation_errors(); ?>
    </div>
</div>

</body>
</html>