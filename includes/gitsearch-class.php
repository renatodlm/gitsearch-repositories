<?php

//Essa classe é usada como template para criação de uma area de options no wp

class GitSearchToken
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {

        add_action('admin_menu', array($this, 'set_custom_fields'));
    }

    //define os paramentros da tela, como slug, nome, url, icone
    public function set_custom_fields()
    {

        add_menu_page('GitSearch', 'GitSearch', 'manage_options', 'gitsearchrepository', 'GitSearchToken::save_custom_fields', '
        dashicons-rest-api', '10');
    }

    //metodo de salvar
    public function save_custom_fields()
    {

        echo "<h3> " . __("Cadastrar Token", "gitsearch") . " </h3>";
        echo "<form method='post'>";

        $campos = array('gitsearchtoken');

        foreach ($campos as  $campo) :

            if (isset($_POST[$campo]))
                update_option($campo, $_POST[$campo]);

            $valor = stripcslashes(get_option($campo));

            echo "
  
                        <p>
  
                            <label> Insira seu token Github aqui: </label></br>
                            <textarea cols='100' rows='4' name='$campo'>$valor</textarea>
                            <br><a href='https://github.com/settings/tokens' target='_blank'>Não tem token?</a>
  
                        </p>
  
                     ";

        endforeach;

        $nomeBotao = (get_option('gitsearchtoken') !== "") ? "Salvar" : "Cadastrar";
        echo "<input type='submit' value='" . $nomeBotao . "''>";
        echo "</form>";
    }
}
