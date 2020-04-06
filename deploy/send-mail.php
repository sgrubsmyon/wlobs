<?php
if($_POST) {
  $recipient = "test@example.org";
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

  $curr_fmt = new NumberFormatter('de_DE', NumberFormatter::CURRENCY);

  /* Construct the order message */
  $order_msg = "
    <p>
      Folgende Bestellung ist bei uns eingegangen:
    </p>
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
    $order_msg = $order_msg . "
      <tr>
        <th>Lieferadresse:</th>
        <td>$visitor_address</a></td>
      </tr>
    ";
  }
  $order_msg = $order_msg . "
        </tbody>
      </table>
    </p>
  ";
  /* Product list: */
  $order_msg = $order_msg . "
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
      $order_msg = $order_msg . "
        <tr>
          <td>" . $item["position"] . "</td>
          <td>" . $item["lieferant_name"] . "</td>
          <td>" . $item["artikel_nr"] . "</td>
          <td>" . $item["artikel_name"] . "</td>
          <td>" . $item["stueckzahl"] . "</td>
          <td>" . $curr_fmt->formatCurrency($item["ges_preis"], "EUR") . "</td>
          <td>" . (is_null($item["ges_pfand"]) ? "—" : $curr_fmt->formatCurrency($item["ges_pfand"], "EUR")) . "</td>
        </tr>
      ";
    }
  $order_msg = $order_msg . "
        </tbody>
      </table>
    </p>
    <p>
      <table class=\"keyvalue summe\">
        <tbody>
          <tr>
            <th>Voraussichtlicher Gesamtbetrag:</th>
            <td>" . $curr_fmt->formatCurrency($bestellung["summe"], "EUR") . "</td>
          </tr>
        </tbody>
      </table>
    </p>
    ";
  if (!empty($visitor_message)) {
    /* Add general message from text box: */
    $order_msg = $order_msg . "
      <h3>Weitere Hinweise de*r Kund*in:</h3>
      <p>$visitor_message</p>
      ";
  }

  /************************************************************************/

  /* Email sending */

  $headers  = 'MIME-Version: 1.0' . "\r\n"
    .'Content-type: text/html; charset=utf-8' . "\r\n"
    .'From: ' . $visitor_email . "\r\n";

  /* Construct the email message */
  $email_greet_msg = "
    <p>
      Liebe Kundin, lieber Kunde,
    </p>

    <p>
      vielen Dank für die Bestellung über unser Webformular!
    </p>

    <p>
      Wir melden uns in Kürze bei Ihnen mit den Details zur Abholung und
      um ggf. Fragen zu klären. Der unten angezeigte Gesamtpreis ist ohne
      Gewähr und dient nur zu ihrer Orientierung. Er könnte noch abweichen,
      wenn z.B. einzelne bestellte Produkte nicht lieferbar sind, es
      Preisänderungen gab oder Sie Artikel im Freitextfeld hinzugefügt haben.
      Gesamtsumme und eventuelle Abweichungen von Ihrer Bestellung teilen wir
      Ihnen so schnell wie möglich mit.
    </p>

    <p>
      Vielen Dank dass Sie den Fairen Handel in Bonn und weltweit auch in
      Corona-Zeiten unterstützen!
    </p>

    <p>
      Bleiben Sie gesund!<br>
      Ihr Team vom Weltladen Bonn e.V.
    </p>
  ";

  $ps_msg = "
    <style>
      p.ps { margin-top: 64px; }
    </style>
    <p class=\"ps\">
      <b>PS:</b> Wir Informieren Sie gerne in einem (Corona-)Ladeninformationen-Newsletter,
      falls sich an unserem Liefer- und Abholangebot etwas ändert und wenn der normale
      Ladenbetrieb wieder aufgenommen wird. Bitte tragen Sie sich dazu unter folgendem Link
      ein:
      <a href=\"https://weltladen-bonn.us8.list-manage.com/subscribe?u=8efbe21878a044bec16a3c8bd&id=a80317e63d\">
        https://weltladen-bonn.us8.list-manage.com/subscribe?u=8efbe21878a044bec16a3c8bd&id=a80317e63d
      </a>
    </p>
  ";

  $visitor_response_msg = "
    <h2>Vielen Dank für Ihre Bestellung!</h2>

    <p>
      Sie haben eine automatische Bestätigungsmail mit den Details Ihrer Bestellung
      und der Bestellnummer erhalten. Bitte prüfen Sie ggf. Ihren Spamverdacht-Ordner,
      falls die Mail nicht angekommen sein sollte.
    </p>

    <p>
      Wir melden uns in Kürze bei Ihnen mit den Details zur Abholung und um ggf. Fragen zu klären.
    </p>

    <p>
      Vielen Dank dass Sie den Fairen Handel in Bonn und weltweit auch in Corona-Zeiten unterstützen!<br>
      Ihr Team vom Weltladen Bonn e.V.
    </p>
  ";

  $recipient_msg = $order_msg;
  $visitor_msg = $email_greet_msg . $order_msg . $ps_msg;

  if (
    mail($recipient, "[Bestellung] Nr. " . $bestellung["nr"], $recipient_msg, $headers) &&
    mail($visitor_email, "Bestellbestätigung Weltladen Bonn" . $bestellung["nr"], $visitor_msg, $headers)
  ) {
    echo $visitor_response_msg . $order_msg . $ps_msg;
  } else {
    echo "<p>Leider gab es ein Problem beim Versenden der Bestellung. Sie können Ihre Bestellung manuell an <a href=\"info@weltladen-bonn.org\">info@weltladen-bonn.org</a> senden.</p>";
  }

} else {
  echo '<p>Etwas ging schief. Wenden Sie sich an <a href=\"info@weltladen-bonn.org\">info@weltladen-bonn.org</a>.</p>';
}
?>
