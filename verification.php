<?php
require("config.php");
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    $tag = $_POST['tag'];
    $response = array("tag" => $tag, "error" => FALSE);
    $query = "SELECT * FROM Auth_users WHERE email = :email";
    $query_params = array(
        ':email' => $_POST['email']
    );
    try {
        $stmt   = $db->prepare($query);
        $result = $stmt->execute($query_params);
    }
    catch (PDOException $ex) {
        $response["error"] = true;
        $response["message"] = "Database Error1. Please Try Again!";
        die(json_encode($response));
    }
   $otp_ok = false;
    $success = false;
    $email_response = false;
   $email = $_POST['email'];
   $statusY = 1;
    $row = $stmt->fetch();
    // Forgot Password
    if ($tag == 'verify_code') {
        if ($row) {
            if ($_POST['otp'] === $row['otp']) {
                $otp_ok = true;
                $stmt = $db->prepare("UPDATE Auth_users SET verified = :status WHERE email = :email");
                $stmt->bindparam(":status", $statusY);
                $stmt->bindparam(":email", $email);
                $stmt->execute();
            }
        }
        $query = "SELECT * FROM Auth_users WHERE email = :email";
        $query_params = array(
            ':email' => $_POST['email']
        );
        try {
            $stmt   = $db->prepare($query);
            $result = $stmt->execute($query_params);
        }
        catch (PDOException $ex) {
            $response["error"] = true;
            $response["message"] = "Database Error1. Please Try Again!";
            die(json_encode($response));
        }
        $user = $stmt->fetch();
        if ($otp_ok == true) {
            $response["error"] = false;
            $response["message"] = "Verify successful!";
            $response["user"]["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["verified"] = $user["verified"];
            $response["user"]["created_at"] = $user["created_at"];
            die(json_encode($response));
        } else {
            $response["error"] = true;
            $response["message"] = "Invalid Credentials!";
            die(json_encode($response));
        }
    }
    // Change Password
    else if ($tag == 'resend_code') {
        $otp = rand(100000, 999999);
        if ($row) {
            $stmt = $db->prepare("UPDATE Auth_users SET otp = :otp WHERE email = :email");
            $stmt->bindparam(":otp", $otp);
            $stmt->bindparam(":email",$email);
            $stmt->execute();
            $success = true;
        }
        if ($success == true) {
            $name = $row['name'];
            $email = $_POST['email'];
            $subject = "SafeKey Email Verification";
            $message = "Hello $name,\n\nVerify that you own $email.\n\nYou may be asked to enter this confirmation code:\n\n$otp\n\nRegards,\nTeam SafeKey.";
            $from = "SafeKey <mail@developervaibhav.in>";
            $headers = "From:" . $from;
            mail($email,$subject,$message,$headers);

            $email_response=true;
            
        }
        if($email_response==true)
        {
            $response["error"] = false;
            $response["message"] = "New otp has been sent to your e-mail address.";
            die(json_encode($response));
        }
    }
    else{
        $response["error"] = false;
            $response["message"] = "Error.";
            die(json_encode($response));
    }
} else {
    echo 'oppss...';
}
?>