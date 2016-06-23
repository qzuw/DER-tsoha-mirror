<?php

class TeraController extends BaseController {

    public static function index($page) {
        $teramaara = Tera::count();
        $sivukoko = 10;
        $sivuja = ceil($teramaara / $sivukoko);

        if (isset($page)) {
            $terat = Tera::all(array('sivu' => $page));
        } else {
            $terat = Tera::all(array());
            $page = 1;
        }

        $data = array('terat' => $terat);

        $data = array_merge($data, self::sivutus($page, $sivuja));

        View::make('tera/listaa_terat.html', $data);
    }

    public static function nayta($blade_id) {
        $tera = Tera::find($blade_id);
        View::make('tera/nayta_tera.html', array('tera' => $tera));
    }

    public static function uusi() {
        self::check_logged_in();
        View::make('tera/lisaa_tera.html');
    }

    public static function lisaa() {
        self::check_logged_in();
        $params = $_POST;
        $attributes = array(
            'valmistaja' => $params['valmistaja'],
            'malli' => $params['malli'],
            'pehmeys' => 0,
            'teravyys' => 0
        );

        $blade = new Tera($attributes);
        $errors = $blade->errors();

        if (count($errors) == 0) {
            $success = $blade->add();
            if ($success) {
                Redirect::to('/nayta_tera/' . $blade->id, array('message' => 'Terä on nyt lisätty tietokantaan'));
            } else {
                Redirect::to('/listaa_terat', array('error' => 'Terän lisääminen tietokantaan epäonnistui, tarkista onko se listassa jo ennestään'));
            }
        } else {
            Redirect::to('/uusi_tera' . $blade->id, array('error' => 'Tiedot eivät ole oikein', 'errors' => $errors, 'attributes' => $attributes));
        }
    }

    public static function nayta_muokkaussivu($blade_id) {
        self::check_logged_in();
        $blade = Tera::find($blade_id);
        View::make('tera/muokkaa_tera.html', array('attributes' => $blade));
    }

    public static function paivita($blade_id) {
        self::check_logged_in();
        $params = $_POST;
        $attributes = array(
            'valmistaja' => $params['valmistaja'],
            'malli' => $params['malli'],
            'pehmeys' => 0,
            'teravyys' => 0
        );

        $blade = Tera::find($blade_id);
        $blade->malli = $params['malli'];
        $blade->valmistaja = $params['valmistaja'];
        $errors = $blade->errors();

        if (count($errors) == 0) {
            $success = $blade->add();
            if ($success) {
                Redirect::to('/nayta_tera/' . $blade->id, array('message' => 'Terä on nyt päivitetty'));
            } else {
                Redirect::to('/nayta_tera/' . $blade->id, array('error' => 'Terän muokkaaminen epäonnistui, se saattaa jo olla käytössä'));
            }
        } else {
            Redirect::to('/muokkaa_tera' . $blade->id, array('error' => 'Tiedot eivät ole oikein', 'errors' => $errors, 'attributes' => $attributes));
        }
    }

    public static function poista($blade_id) {
        self::check_logged_in();
        $blade = Tera::find($blade_id);
        $blade_name = $blade->valmistaja . " " . $blade->malli;
        $success = $blade->delete();
        if ($success) {
            Redirect::to('/listaa_terat', array('success' => 'Terä ' . $blade_name . ' on nyt poistettu tietokannasta'));
        } else {
            Redirect::to('/nayta_tera/' . $blade_id, array('error' => 'Terän poistaminen epäonnistui, se on käytössä'));
        }
    }

}
