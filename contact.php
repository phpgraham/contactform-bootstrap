<?php
/**
* Simple contact form demo, w3c compliant, html and php validation, error notifications, smtp email, bot secure
* using bootstrap 4
* uses multiple spam protection techniques
* another using php7, html5, jquery - https://github.com/phpgraham/contactform
* another using bootstrap 4 and vue - http://#
* another using laravel and angularjs - http://#
* another using nodejs - http://#
**/

//Load composer's autoloader
//used to load PHPMailer and vlucas Dotenv
require '../vendor/autoload.php';

//load environment and email variables
$dotenv = new Dotenv\Dotenv('../');
$dotenv->load();

require('Classes/mail.php');

// define variables and set to empty values
$name = $email = $message = $loadtime = $human = $errorMsg = "";
$errMess = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // avoid polluting the global variable space, call form handler then extract variables
  $response = validateContactForm($_POST);
  if($response=='success'){
    $success = 'Your message has been successfully sent. We will contact you soon.';
  } else {
    $errorMsg = $response;
  }
}

//sever side validation just in case javascript validation is not running
function validateContactForm($arr) {
  extract($arr);
  //check for spam bots
  //two hidden elements, time trap, email in name field - multiple methods to really annoy the bots
  //clean inputs, check for empty required fields
  //use html and php validation with just simple error notification array
  if (empty($form_email)) {
    $errMess[] = "Name is required";
  } else {
    $form_email = clean_input($form_email);
    //just simple for now, could add special chars ('-,^) etc
    if (!preg_match("/^[a-zA-Z ]*$/",$form_email)) {
      $errMess[] = "Only letters and white space allowed for name";
    }
  }

  if (empty($form_name)) {
    $errMess[] = "Email is required";
  } else {
    //just simple email check for now, could add checkdnsrr and FILTER_SANITIZE_EMAIL checks
    $form_name = clean_input($form_name);
    if (!filter_var($form_name, FILTER_VALIDATE_EMAIL)) {
      $errMess[] = "Invalid email format";
    }
  }

  //simple check for now, could add min char check
  if (empty($form_message)) {
    $form_message = "";
  } else {
    $form_message = clean_input($form_message);
  }

  //bot check - time trap
  $totaltime = time() - $loadtime;
  if($totaltime < 5) {
      $errMess[] = "That was quick, are you human. Please take time to fill in the form before submitting.";
  }

  //bot check hidden field
  if (!empty($form_human) && !($form_human == "10" || strtolower($form_human) == "ten")) {
      $errMess[] = "Are you human, leave field blank or complete sum.";
  }

  //bot check hidden field
  if (!empty($url)) {
      $errMess[] = "Are you human, leave field blank.";
  }

  if(isset($errMess) && $errMess){
    //validation errors
    return $errMess;
  } else {
    // process response, send email, return success message
    // PHPMailer returns success or error messages
    $mail = new Mail();

    $sendemail = $mail->sendMail(
        $form_name,
        $form_email,
        'Message received from Contact Form',
        '<h1>This is the HTML message</h1><p>'.$form_message.'</p>',
        $form_message
    );

    if($sendemail=='success') {
      // email sent successfully
      return 'success';
    } else {
      // mailing error
       return $sendemail;
    }
  }
}

function clean_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>
<!DOCTYPE HTML>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contact Form - Bootstrap</title>

<!-- Bootstrap core CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
<!-- Custom styles for this template -->
<link href="style.css" rel="stylesheet">
</head>

<body>
  <header class="body">
    <h1>Contact Form Demo</h1>
    <p>Demo code using bootstrap 4</p>
  </header>

  <main class="container">
    <form id="contactForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

      <?php if(isset($errorMsg) && $errorMsg) { ?>
        <div class="alert alert-danger" role="alert">
          <?php foreach($errorMsg as $error) { ?>
            <p class="mb-0"><span class="fa fa-times-circle">&nbsp;</span><?=htmlspecialchars($error)?></p>
          <?php } ?>
        </div>
      <?php } ?>
      <?php if(isset($success) && $success) { ?>
        <div class="alert alert-success" role="alert">
            <span class="fa fa-check-circle">&nbsp;</span><?=htmlspecialchars($success)?>
        </div>
      <?php } ?>

      <div class="form-group">
        <label for="form_email">Name</label>
        <input name="form_email" type="text" class="form-control" id="form_email" aria-describedby="nameHelp" placeholder="Enter name" aria-required="true" value="<?php if(isset($_POST['form_email']) && !isset($success)) echo htmlspecialchars($_POST['form_email']); ?>" required>
        <small id="nameHelp" class="form-text text-muted">Please enter your contact name.</small>
        <div class="invalid-feedback">
          Name is required with only letters and space characters.
        </div>
      </div>
      <div class="form-group">
        <label for="form_name">Email</label>
        <input name="form_name" type="email" class="form-control" id="form_name" aria-describedby="emailHelp" placeholder="Enter email" aria-required="true" value="<?php if(isset($_POST['form_name']) && !isset($success)) echo htmlspecialchars($_POST['form_name']); ?>" required>
        <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
        <div class="invalid-feedback">
          A valid Email is required.
        </div>
      </div>
      <div class="form-group">
        <label for="form_message">Message</label>
        <textarea name="form_message" class="form-control" id="form_message" rows="5" aria-describedby="contactHelp" placeholder="Enter your message here"><?php if(isset($_POST['form_message']) && !isset($success)) echo htmlspecialchars($_POST['form_message']); ?></textarea>
        <small id="contactHelp" class="form-text text-muted">Please enter a message.</small>
        <div class="invalid-feedback">
          Message is required with a minimum of 10 characters.
        </div>
      </div>
      <div class="form-group inputhere" id="inputhere">
        <label for="form_human">What is five plus five ? (Anti-spam)</label>
        <input name="form_human" placeholder="Type Here" id="form_human">

        <label for="form_url">Url: </label>
        <input type="text" name="url" id="form_url" value="<?php if(isset($_POST['url'])) echo htmlspecialchars($_POST['url']); ?>" value="<?php if(isset($_POST['url']) && !isset($success)) echo htmlspecialchars($_POST['url']); ?>">
      </div>

      <input type="hidden" name="loadtime" value="<?php echo time(); ?>">
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </main>

  <footer class="body">
  </footer>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js" crossorigin="anonymous"></script>
<script>
  $(document).ready(
    function() {
      $('#inputhere').hide();
    }
  );
  /* jquery form validation, hooked to bootstrap is-valid form feedback class and elements */
  jQuery.validator.addMethod("lettersonly", function(value, element) {
    return this.optional(element) || /^[a-z," "]+$/i.test(value);
  }, null);

  $("#contactForm").validate({
    rules: {
        form_email:{
            required:true,
            lettersonly:true
        },
        form_name:{
            required:true,
            email:true
        },
        form_message:{
            required:true,
            minlength:10
        }
    },
    success: function (element) {
        element.removeClass('is-invalid').addClass('is-valid');
    },
    highlight: function(element) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function(element) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    },
    errorPlacement: function(error, element) { },

    submitHandler: function(form,event){
        form.submit();
    }

  });
</script>
</body>
</html>
