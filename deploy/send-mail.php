<?php
if($_POST) {
  $recipient = "markus.voge@gmx.de";
  $visitor_name = "";
  $visitor_email = "";
  $visitor_phone = "";
  $visitor_address = "";
  $visitor_message = "";
  $datenschutz = "";
  $hygiene = "";
  $lieferung = "";

  if (isset($_POST['visitor_name'])) {
    $visitor_name = filter_var($_POST['visitor_name'], FILTER_SANITIZE_STRING);
  }

  if (isset($_POST['visitor_email'])) {
    $visitor_email = str_replace(array("\r", "\n", "%0a", "%0d"), '', $_POST['visitor_email']);
    $visitor_email = filter_var($visitor_email, FILTER_VALIDATE_EMAIL);
  }

  if (isset($_POST['visitor_phone'])) {
    $visitor_phone = str_replace(array("\r", "\n", "%0a", "%0d"), '', $_POST['visitor_phone']);
    $visitor_phone = filter_var($visitor_phone, FILTER_SANITIZE_STRING);
  }

  if(isset($_POST['visitor_address'])) {
    $visitor_address = filter_var($_POST['visitor_address'], FILTER_SANITIZE_STRING);
  }

  if (isset($_POST['visitor_message'])) {
    $visitor_message = htmlspecialchars($_POST['visitor_message']);
  }

  /* Process data to be sent to API from POST request (form submit) */
  $data = array('details' => array());

  $still_processing = true;
  $position = 0;
  while ($still_processing) {
    $position++;
    if (array_key_exists('product'.$position, $_POST)) {
      $stueck = $_POST['stueck'.$position];
      list($lief, $artnr) = explode("______", $_POST['product'.$position]); // extract lieferant name and article number from composite string
      if ($stueck > 0) {
        array_push($data['details'], array(
          "position" => $position,
          "stueckzahl" => $stueck,
          "lieferant_name" => $lief,
          "artikel_nr" => $artnr
        ));
      }
    } else {
      $still_processing = false;
    }
  }

  /* Send order data to DB and create new entries there,
     API will return data to put into email if successful
     (https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php) */
  $options = array(
      'http' => array(
          'method'  => 'POST',
          'header'  => "Content-Type: application/json\r\n",
          'content' => json_encode($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents('http://127.0.0.1/coronashopper/api/bestellung/create.php', false, $context);
  if (FALSE === $result) {
    exit("Failed to open stream to API");
  }
  $bestellung = json_decode($result, true);

  $headers  = 'MIME-Version: 1.0' . "\r\n"
    .'Content-type: text/html; charset=utf-8' . "\r\n"
    .'From: ' . $visitor_email . "\r\n";

  /* Construct the email message */
  $message = "
    <style>
      table { border-collapse: collapse; }
      table, th, td { border: 1px solid black; }
      th, td { padding: 5px; }
      table.keyvalue th { text-align: right; }
      table.summe, table.summe th, table.summe td { border: none; }
    </style>
    <h2>Bestellung</h2>
    <h3>Kontaktdaten</h3>
    <p>
      <table class=\"keyvalue\">
        <tbody>
          <tr>
            <th>Name:</th>
            <td>$visitor_name</td>
          </tr>
          <tr>
            <th>E-Mail:</th>
            <td><a href=\"mailto:$visitor_email\">$visitor_email</a></td>
          </tr>
          <tr>
            <th>Tel.-Nr.:</th>
            <td>$visitor_phone</a></td>
          </tr>
  ";
  if (!empty($visitor_address)) {
    $message = $message . "
      <tr>
        <th>Lieferadresse:</th>
        <td>$visitor_address</a></td>
      </tr>
    ";
  }
  $message = $message . "
        </tbody>
      </table>
    </p>
  ";
  /* Product list: */
  $message = $message . "
    <h3>Bestelldaten</h3>
    <p>
      <table class=\"keyvalue\">
        <tbody>
          <tr>
            <th>Bestell-Nr.:</th>
            <td>" . $bestellung["nr"] . "</td>
          </tr>
          <tr>
            <th>Zeit:</th>
            <td>" . $bestellung["datum"] . "</td>
          </tr>
        </tbody>
      </table>
    </p>
    <p>
      <table>
        <tbody>
          <tr>
            <th>Position</th>
            <th>Lieferant</th>
            <th>Artikel-Nr.</th>
            <th>Artikelname</th>
            <th>Stück</th>
            <th>Preis</th>
            <th>Pfand</th>
          </tr>
    ";
    foreach ($bestellung["details"] as $item) {
      $message = $message . "
        <tr>
          <td>" . $item["position"] . "</td>
          <td>" . $item["lieferant_name"] . "</td>
          <td>" . $item["artikel_nr"] . "</td>
          <td>" . $item["artikel_name"] . "</td>
          <td>" . $item["stueckzahl"] . "</td>
          <td>" . $item["ges_preis"] . "</td>
          <td>" . (is_null($item["ges_pfand"]) ? "" : $item["ges_pfand"]) . "</td>
        </tr>
      ";
    }
  $message = $message . "
        </tbody>
      </table>
    </p>
    <p>
      <table class=\"keyvalue summe\">
        <tbody>
          <tr>
            <th>Voraussichtlicher Gesamtbetrag:</th>
            <td>" . $bestellung["summe"] . "</td>
          </tr>
        </tbody>
      </tbody>
    </p>
    ";
  if (!empty($visitor_message)) {
    /* Add general message from text box: */
    $message = $message . "
      <h3>Weitere Hinweise de*r Kund*in:</h3>
      <p>$visitor_message</p>
      ";
  }

  echo $message;
  // if(mail($recipient, "[Bestellung] Nr. " . $bestellung["nr"], $message, $headers)) {
  //   echo "<p>Thank you for contacting us, $visitor_name. You will get a reply within 24 hours.</p>";
  //   echo $message;
  // } else {
  //   echo '<p>We are sorry but the email did not go through.</p>';
  // }

} else {
  echo '<p>Something went wrong</p>';
}
?>
