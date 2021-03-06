<?php

class Partahoyla extends BaseModel {

    public $id, $valmistaja, $malli, $aggressiivisuus, $viittauksia;

    public function __construct($attributes) {
        parent::__construct($attributes);
        $this->validators = array('validate_manufact', 'validate_model', 'validate_aggressiveness');
    }

    public static function all($options) {
        $page = self::page_from_options($options);
        $limit = self::limit_from_options($options);

        $offset = $limit * ($page - 1);

        try {
            if ($limit == 0) {
                $query = DB::connection()->prepare('SELECT * FROM Hoylanakyma ORDER BY viittauksia DESC');
                $query->execute(array());
            } else {
                $query = DB::connection()->prepare('SELECT * FROM Hoylanakyma ORDER BY viittauksia DESC LIMIT :limit OFFSET :offset');
                $query->execute(array('limit' => $limit, 'offset' => $offset));
            }
            $rows = $query->fetchAll();
            $hoylat = array();
            foreach ($rows as $row) {
                $hoylat[] = new Partahoyla(array(
                    'id' => $row['id'],
                    'valmistaja' => $row['valmistaja'],
                    'malli' => $row['malli'],
                    'aggressiivisuus' => $row['aggressiivisuus'],
                    'viittauksia' => $row['viittauksia']
                ));
            }
            return $hoylat;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function owned($options) {
        $user_id = $_SESSION['tunnus'];
        $page = self::page_from_options($options);
        $limit = self::limit_from_options($options);

        $offset = $limit * ($page - 1);

        try {
            if ($limit == 0) {
                $query = DB::connection()->prepare('SELECT * FROM Hoylanakyma hn JOIN Kayttajanhoylat AS kh ON kh.partahoyla_id = hn.id WHERE kh.kayttaja_id = :id ORDER BY viittauksia DESC');
                $query->execute(array('id' => $user_id));
            } else {
                $query = DB::connection()->prepare('SELECT * FROM Hoylanakyma hn JOIN Kayttajanhoylat AS kh ON kh.partahoyla_id = hn.id WHERE kh.kayttaja_id = :id ORDER BY viittauksia DESC LIMIT :limit OFFSET :offset');
                $query->execute(array('id' => $user_id, 'limit' => $limit, 'offset' => $offset));
            }
            $rows = $query->fetchAll();
            $razors = array();
            foreach ($rows as $row) {
                $razors[] = new Partahoyla(array(
                    'id' => $row['id'],
                    'valmistaja' => $row['valmistaja'],
                    'malli' => $row['malli'],
                    'aggressiivisuus' => $row['aggressiivisuus'],
                    'viittauksia' => $row['viittauksia']
                ));
            }
            return $razors;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function count() {
        $query = DB::connection()->prepare('SELECT count(*) AS maara FROM Partahoyla');
        $query->execute();
        $row = $query->fetch();

        if ($row) {
            $amount = $row['maara'];
            return $amount;
        }
        return null;
    }

    public static function count_owners($razor_id) {
        $query = DB::connection()->prepare('SELECT count(*) AS maara FROM Kayttajanhoylat WHERE partahoyla_id = :razor_id');
        try {
            $query->execute(array('razor_id' => $razor_id));
            $row = $query->fetch();

            if ($row) {
                $amount = $row['maara'];
                return $amount;
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function find($id) {
        try {
            $query = DB::connection()->prepare('SELECT * FROM Hoylanakyma WHERE id = :id');
            $query->execute(array('id' => $id));
            $row = $query->fetch();
        } catch (Exception $e) {
            return null;
        }

        if ($row) {
            $razor = new Partahoyla(array(
                'id' => $row['id'],
                'valmistaja' => $row['valmistaja'],
                'malli' => $row['malli'],
                'aggressiivisuus' => $row['aggressiivisuus'],
                'viittauksia' => $row['viittauksia']
            ));
            return $razor;
        }
        return null;
    }

    public function add() {
        $query = DB::connection()->prepare('INSERT INTO Partahoyla (valmistaja, malli) VALUES (:valmistaja, :malli) RETURNING id');
        try {
            $query->execute(array('valmistaja' => $this->valmistaja, 'malli' => $this->malli));
            $row = $query->fetch();
            $this->id = $row['id'];
            $this->viittauksia = 0;
            $this->aggressiivisuus = 0;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete() {
        if ($this->viittauksia == 0) {
            $query = DB::connection()->prepare('DELETE FROM Partahoyla WHERE id = :id');
            try {
                $query->execute(array('id' => $this->id));
                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function update() {
        if ($this->viittauksia == 0 && self::count_owners($this->id) == 0) {
            $query = DB::connection()->prepare('UPDATE Partahoyla SET valmistaja = :valmistaja, malli = :malli WHERE id = :id');
            try {
                $query->execute(array('id' => $this->id, 'valmistaja' => $this->valmistaja, 'malli' => $this->malli));
                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function validate_manufact() {
        $errors = array();

        $errors = array_merge($errors, $this->validate_string_not_empty('Valmistaja', $this->valmistaja));
        $errors = array_merge($errors, $this->validate_string_length('Valmistaja', $this->valmistaja, 3));

        return $errors;
    }

    public function validate_model() {
        $errors = array();

        $errors = array_merge($errors, $this->validate_string_not_empty('Malli', $this->malli));
        $errors = array_merge($errors, $this->validate_string_length('Malli', $this->malli, 3));

        return $errors;
    }

    public function validate_aggressiveness() {
        $errors = array();

        $errors = array_merge($errors, $this->validate_string_is_number('Aggressiivisuus', $this->aggressiivisuus));

        return $errors;
    }

}
