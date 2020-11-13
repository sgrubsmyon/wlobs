<?php
if ($_POST) {
  $recipient = "info@weltladen-bonn.org";
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

  $datenschutz = "Nein";
  if (isset($_POST['datenschutz'])) {
    $datenschutz = $_POST['datenschutz'] ? "Ja" : "Nein";
  }

  $hygiene = "Nein";
  if (isset($_POST['hygiene'])) {
    $hygiene = $_POST['hygiene'] ? "Ja" : "Nein";
  }

  $lieferung = "Nein";
  if (isset($_POST['lieferung'])) {
    $lieferung = $_POST['lieferung'] ? "Ja" : "Nein";
  }

  /* Process data to be sent to API from POST request (form submit) */
  $data = array('details' => array());

  /* https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php */
  function startsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
  }
  // function endsWith($haystack, $needle) {
  //     return substr_compare($haystack, $needle, -strlen($needle)) === 0;
  // }

  // echo '<pre>'; print_r($_POST); echo '</pre>';
  $types = array_filter(array_keys($_POST), function($k) {
    return startsWith($k, 'product-');
  });
  $types = array_map(function($typ) {
    $matches = array();
    preg_match('/^product-([a-z]+)-/', $typ, $matches);
    return $matches[1]; // strtoupper(): not now!
  }, $types);
  $types = array_unique($types);

  $position = 0;
  foreach ($types as $typ) {
    $still_processing = true;
    $typ_position = 0;
    while ($still_processing) {
      $position++;
      $typ_position++;
      if (array_key_exists('product-'.$typ.'-'.$typ_position, $_POST)) {
        $stueck = $_POST['stueck-'.$typ.'-'.$typ_position];
        list($lief, $artnr) = explode("______", $_POST['product-'.$typ.'-'.$typ_position]); // extract lieferant name and article number from composite string
        if ($stueck > 0) {
          array_push($data['details'], array(
            "position" => $position,
            "stueckzahl" => $stueck,
            "lieferant_name" => $lief,
            "artikel_nr" => $artnr
          ));
        }
      } else {
        $position--;
        $still_processing = false;
      }
    }
  }
  // echo '<pre>'; print_r($data); echo '</pre>';
  // die();

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
  $result = file_get_contents('https://www.weltladen-bonn.org/bestellung/api/bestellung/create.php', false, $context); // for production deployment
  // $result = file_get_contents('http://localhost/wlobs/api/bestellung/create.php', false, $context); // for local development testing
  if (FALSE === $result) {
    exit("Failed to open stream to API");
  }
  $bestellung = json_decode($result, true);
  // echo '<pre>'; print_r($bestellung); echo '</pre>';
  if ($bestellung["message"] != "Bestellung was created.") {
    exit("Error: " . $bestellung["message"]);
  }

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
  $order_msg_internal = $order_msg;
  if (!empty($visitor_address)) {
    $new_part = "
      <tr>
        <th>Lieferadresse:</th>
        <td>$visitor_address</a></td>
      </tr>
    ";
    $order_msg = $order_msg . $new_part;
    $order_msg_internal = $order_msg_internal . $new_part;
  }
  $new_part = "
        </tbody>
      </table>
    </p>
  ";
  $order_msg = $order_msg . $new_part;
  $order_msg_internal = $order_msg_internal . $new_part;
  /* Product list: */
  $new_part = "
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
          <tr>
            <th>Zustimmung zur Verwendung meiner Daten?</th>
            <td>" . $datenschutz . "</td>
          </tr>
          <tr>
            <th>Zustimmung zur Einhaltung der Hygieneregeln?</th>
            <td>" . $hygiene . "</td>
          </tr>
          <tr>
            <th>Lieferung erforderlich?</th>
            <td>" . $lieferung . "</td>
          </tr>
        </tbody>
      </table>
    </p>
    <p>
      <table>
        <tbody>
          <tr>
            <th>Position</th>
  ";
  $order_msg = $order_msg . $new_part;
  $order_msg_internal = $order_msg_internal . $new_part;
  $new_part = "
            <th>Typ</th>
            <th>Sortiment</th>
  ";
  $order_msg_internal = $order_msg_internal . $new_part;
  $new_part = "
            <th>Lieferant</th>
            <th>Artikel-Nr.</th>
            <th>Artikelname</th>
            <th>Stück</th>
            <th>Preis</th>
            <th>Pfand</th>
            <th>MwSt.</th>
          </tr>
  ";
  $order_msg = $order_msg . $new_part;
  $order_msg_internal = $order_msg_internal . $new_part;
  foreach ($bestellung["details"] as $item) {
    $new_part = "
          <tr>
            <td>" . $item["position"] . "</td>
    ";
    $order_msg = $order_msg . $new_part;
    $order_msg_internal = $order_msg_internal . $new_part;
    $new_part = "
            <td>" . $item["typ"] . "</td>
            <td>" . ($item["sortiment"] == "1" ? "Ja" : "Nein") . "</td>
    ";
    $order_msg_internal = $order_msg_internal . $new_part;
    $new_part = "
            <td>" . $item["lieferant_name"] . "</td>
            <td>" . $item["artikel_nr"] . "</td>
            <td>" . $item["artikel_name"] . "</td>
            <td>" . $item["stueckzahl"] . "</td>
            <td>" . $curr_fmt->formatCurrency($item["ges_preis"], "EUR") . "</td>
            <td>" . (is_null($item["ges_pfand"]) ? "—" : $curr_fmt->formatCurrency($item["ges_pfand"], "EUR")) . "</td>
            <td>" . ($item["mwst_satz"] * 100) . "%</td>
          </tr>
    ";
    $order_msg = $order_msg . $new_part;
    $order_msg_internal = $order_msg_internal . $new_part;
  }
  $new_part = "
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
  $order_msg = $order_msg . $new_part;
  $order_msg_internal = $order_msg_internal . $new_part;
  if (!empty($visitor_message)) {
    /* Add general message from text box: */
    $new_part = "
  <h3>Weitere Hinweise de*r Kund*in:</h3>
  <p>$visitor_message</p>
    ";
    $order_msg = $order_msg . $new_part;
    $order_msg_internal = $order_msg_internal . $new_part;
  }

  /************************************************************************/

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
      Gewähr und dient nur zu Ihrer Orientierung. Er könnte noch abweichen,
      wenn z.B. einzelne bestellte Produkte nicht lieferbar sind, es
      Preisänderungen gab oder Sie Artikel im Freitextfeld hinzugefügt haben.
      Gesamtsumme und eventuelle Abweichungen von Ihrer Bestellung teilen wir
      Ihnen so schnell wie möglich mit.
    </p>

    <p>
      Bei Fragen Ihrerseits können Sie sich an <a href=\"info@weltladen-bonn.org\">info@weltladen-bonn.org</a>
      wenden.
    </p>

    <p>
      Vielen Dank, dass Sie den Fairen Handel in Bonn und weltweit auch in
      Corona-Zeiten unterstützen!
    </p>

    <p>
      Bleiben Sie gesund!<br>
      Ihr Team vom Weltladen Bonn e.V.
    </p>

    <p>
      Folgende Bestellung ist bei uns eingegangen:
    </p>
  ";

  $ps_msg = "
    <style>
      p.ps { margin-top: 40px; }
    </style>
    <p class=\"ps\">
      <b>PS:</b> Wir Informieren Sie gerne in einem Newsletter...
    </p>
  ";

  $visitor_response_msg = "
    <h2>Vielen Dank für Ihre Bestellung!</h2>

    <p>
      <a href='index.html'>← Zurück zum Bestellformular</a>
    </p>

    <p>
      Sie haben eine automatische Bestätigungsmail mit den Details Ihrer Bestellung
      und der Bestellnummer erhalten. Bitte prüfen Sie ggf. Ihren Spamverdacht-Ordner,
      falls die Mail nicht angekommen sein sollte.
    </p>

    <p>
      Wir melden uns in Kürze bei Ihnen mit den Details zur Abholung und um ggf. Fragen zu klären.
      Bei Fragen Ihrerseits können Sie sich an <a href=\"info@weltladen-bonn.org\">info@weltladen-bonn.org</a>
      wenden.
    </p>

    <p>
      Vielen Dank, dass Sie den Fairen Handel in Bonn und weltweit auch in Corona-Zeiten unterstützen!<br>
      Ihr Team vom Weltladen Bonn e.V.
    </p>

    <p>
      <a href='index.html'>← Zurück zum Bestellformular</a>
    </p>

    <p>
      Folgende Bestellung ist bei uns eingegangen:
    </p>
  ";

  $recipient_msg = $order_msg_internal;
  $visitor_msg = $email_greet_msg . $order_msg; // . $ps_msg

  /* Email sending */

  $headers  = "MIME-Version: 1.0" . "\r\n"
    . "Content-type: text/html; charset=utf-8" . "\r\n"
    . "From: Weltladen Bonn <" . $recipient . ">\r\n";

  if (
    true
    // mail($recipient, "[Bestellung] Nr. " . $bestellung["nr"], $recipient_msg, $headers) &&
    // mail($visitor_email, "Bestellbestätigung Weltladen Bonn", $visitor_msg, $headers)
  ) {
    echo $visitor_response_msg . $order_msg; // . $ps_msg
  } else {
    echo "
      <h2>Ein Fehler ist aufgetreten</h2>
      <p>
        Leider gab es ein Problem beim Versenden der Bestellung. Die Bestellung hat den Weltladen nicht erreicht.
        Bitte senden Sie Ihre Bestellung manuell an <a href=\"info@weltladen-bonn.org\">info@weltladen-bonn.org</a>.
      </p>
    ";
    echo "<p>Hier die Details zu Ihrer Bestellung:</p>";
    echo $order_msg;
  }

} else {
  echo '<p>Etwas ging schief. Wenden Sie sich an <a href=\"info@weltladen-bonn.org\">info@weltladen-bonn.org</a>.</p>';
}
?>
