<?php
 
if($_POST) {
    $recipient = "markus.voge@gmx.de";
    $visitor_name = "";
    $visitor_email = "";
    $address = "";
    $concerned_department = "";
    $visitor_message = "";

    if(isset($_POST['visitor_name'])) {
        $visitor_name = filter_var($_POST['visitor_name'], FILTER_SANITIZE_STRING);
    }

    if(isset($_POST['visitor_email'])) {
        $visitor_email = str_replace(array("\r", "\n", "%0a", "%0d"), '', $_POST['visitor_email']);
        $visitor_email = filter_var($visitor_email, FILTER_VALIDATE_EMAIL);
    }

    if(isset($_POST['visitor_message'])) {
        $visitor_message = htmlspecialchars($_POST['visitor_message']);
    }

    
    $headers  = 'MIME-Version: 1.0' . "\r\n"
    .'Content-type: text/html; charset=utf-8' . "\r\n"
    .'From: ' . $visitor_email . "\r\n";
    
    /* Construct the email message */
    /* Meta-data: */
    $timestamp = date('d.m.Y H:i:s', time());
    $message = "
    <h2>Bestellung</h2>
    <h3>Allgemeine Daten</h3>
    <table>
      <tbody>
        <tr>
          <th>Zeit:</th>
          <td>$timestamp</td>
        </tr>
        <tr>
          <th>Name:</th>
          <td>$visitor_name</td>
        </tr>
        <tr>
          <th>E-Mail:</th>
          <td><a href='mailto:$visitor_email'>$visitor_email</a></td>
        </tr>
      </tbody>
    </table>
    ";
    /* Product list: */
    $message = $message . "
    <h3>Produktliste</h3>
    <table>
      <tbody>
        <tr>
          <th>Produkt</th>
          <th>Stückzahl</th>
        </tr>
    ";
    $still_processing = true;
    $i = 0;
    while ($still_processing) {
      $i++;
      if (array_key_exists("product".$i, $_POST)) {
        $prod = $_POST["product".$i];
        $quant = $_POST["quantity".$i];
        if ($quant > 0) {
          $message = $message . "
              <tr>
                <td>$prod</td>
                <td>$quant</td>
              </tr>
          ";
        }
      } else {
        $still_processing = false;
      }
    }
    $message = $message . "
      </tbody>
    </table>
    ";
    if (strlen($visitor_message) > 0) {
      /* Add general message from text box: */
      $message = $message . "
      <h3>Weitere Hinweise de*r Kund*in:</h3>
      <p>$visitor_message</p>
      ";
    }
    
    echo $message;
    // if(mail($recipient, "[Bestellung]", $visitor_message, $headers)) {
    //     echo "<p>Thank you for contacting us, $visitor_name. You will get a reply within 24 hours.</p>";
    // } else {
    //     echo '<p>We are sorry but the email did not go through.</p>';
    // }

} else {
    echo '<p>Something went wrong</p>';
}

?>