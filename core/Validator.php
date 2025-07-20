<?php
/**
 * Classe de validation des données
 * SmartAccess UCB - Université Catholique de Bukavu
 */

class Validator {
    
    /**
     * Valider un matricule UCB
     */
    public static function isValidMatricule($matricule) {
        return preg_match('/^\d{2}\/\d{2}\.\d{5}$/', $matricule);
    }

    /**
     * Valider un email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valider que le champ n'est pas vide
     */
    public static function required($value) {
        return !empty(trim($value));
    }

    /**
     * Valider la longueur minimale
     */
    public static function minLength($value, $min) {
        return strlen(trim($value)) >= $min;
    }

    /**
     * Valider la longueur maximale
     */
    public static function maxLength($value, $max) {
        return strlen(trim($value)) <= $max;
    }

    /**
     * Valider un nombre entier
     */
    public static function isInteger($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Valider une date
     */
    public static function isValidDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Valider que la date de fin est après la date de début
     */
    public static function isDateAfter($dateEnd, $dateStart) {
        $start = new DateTime($dateStart);
        $end = new DateTime($dateEnd);
        return $end > $start;
    }

    /**
     * Nettoyer et sécuriser une chaîne
     */
    public static function sanitizeString($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valider un ensemble de règles
     */
    public static function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';

            foreach ($fieldRules as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if ($ruleValue && !self::required($value)) {
                            $errors[$field][] = "Le champ $field est requis";
                        }
                        break;

                    case 'email':
                        if ($ruleValue && !empty($value) && !self::isValidEmail($value)) {
                            $errors[$field][] = "Le champ $field doit être un email valide";
                        }
                        break;

                    case 'matricule':
                        if ($ruleValue && !empty($value) && !self::isValidMatricule($value)) {
                            $errors[$field][] = "Le format du matricule est invalide (XX/YY.ZZZZZ)";
                        }
                        break;

                    case 'min_length':
                        if (!empty($value) && !self::minLength($value, $ruleValue)) {
                            $errors[$field][] = "Le champ $field doit contenir au moins $ruleValue caractères";
                        }
                        break;

                    case 'max_length':
                        if (!empty($value) && !self::maxLength($value, $ruleValue)) {
                            $errors[$field][] = "Le champ $field ne peut pas dépasser $ruleValue caractères";
                        }
                        break;

                    case 'integer':
                        if ($ruleValue && !empty($value) && !self::isInteger($value)) {
                            $errors[$field][] = "Le champ $field doit être un nombre entier";
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}