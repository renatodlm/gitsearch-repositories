<?php

function shortcode_gitsearchRepositories($attrs, $cotent)
{
    //EXIGE A CHAVE DE AUTENTICAÇÃO
    if (get_option('gitsearchtoken')) :
        extract(shortcode_atts(array(), $attrs));


        $html = '<div class="gitsearch-container">';
        $html .= '<div class="gitsearch-row">';

        //TELA DE BUSCA PRINCIPAL SEM NENHUM PARAMETRO
        if (!isset($_GET['gitSearchRepository']) && !isset($_GET['gitSearchRepo']) && !isset($_GET['gitSearchOwner'])) :
            $html .= '<div class="gitsearch-col-12">';
            $html .= '<div class="gitsearch-container-search">';
            $html .= '<form class="gitsearch-form" action="" method="GET">';
            $html .= '<p><label for="gitsearch-repositories">' . esc_html__('BUSCAR REPOSITÓRIO:', 'gitsearch') . '</label></p>';
            $html .= '<input id="gitsearch-repositories" name="gitSearchRepository" type="text" required>';
            $html .= '<input id="gitsearch-submit" type="submit" value="' . esc_html__('buscar', 'gitsearch') . '">';
            $html .= '<ul id="gitsearch-suggestions"></ul>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '<div id="gitsearch-favorites" class="gitsearch-favorites">';
            $html .= '<div class="gitserach-favorites-list-container">';
            $html .= '<div>' . esc_html__('Itens em favoritos', 'gitsearch') . '<i class="fa fa-shopping-favorites" aria-hidden="true"></i> <span id="favoritesCounter" class="gitsearch-badge">0</span></div>';
            $html .= '<button class="gitsearch-button-defaut" id="openfavorites">' . esc_html__('atualizar favoritos', 'gitsearch') . '</button>';
            $html .= '</div>';

            $html .= '<div class="gitserach-favorites-list-container">';
            $html .= '<h3>' . esc_html__('FAVORITOS', 'gitsearch') . '</h3>';
            $html .= '<ul id="gitsearch-favorites-content"></ul>';
            $html .= '<button type="button" id="clear_favorites" class="gitsearch-button-defaut">' . esc_html__('limpar favoritos', 'gitsearch') . '</button>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        else :
            $html .= '<div class="gitsearch-col-4">';
            $html .= '<div class="gitsearch-container-search">';
            //TELA DE BUSCA DE REPOSITÓRIOS
            $gitSearchRepository = (isset($_GET['gitSearchRepository'])) ? $_GET['gitSearchRepository'] : '';

            $html .= '<form class="gitsearch-form" action="" method="GET">';
            $html .= '<p><label for="gitsearch-repositories">' . esc_html__('BUSCAR NOVO REPOSITÓRIO:', 'gitsearch') . '</label></p>';
            $html .= '<input id="gitsearch-repositories" name="gitSearchRepository" type="text" value="' . $gitSearchRepository . '" required>';
            $html .= '<input id="gitsearch-submit" type="submit" value="' . esc_html__('buscar', 'gitsearch') . '">';
            $html .= '<ul id="gitsearch-suggestions"></ul>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '<div id="gitsearch-favorites" class="gitsearch-favorites">';
            $html .= '<div class="gitserach-favorites-list-container">';
            $html .= '<div>' . esc_html__('Itens em favoritos', 'gitsearch') . '<i class="fa fa-shopping-favorites" aria-hidden="true"></i> <span id="favoritesCounter" class="gitsearch-badge">0</span></div>';
            $html .= '<button class="gitsearch-button-defaut" id="openfavorites">' . esc_html__('atualizar favoritos', 'gitsearch') . '</button>';
            $html .= '</div>';

            $html .= '<div class="gitserach-favorites-list-container">';
            $html .= '<h3>' . esc_html__('FAVORITOS', 'gitsearch') . '</h3>';
            $html .= '<ul id="gitsearch-favorites-content"></ul>';
            $html .= '<button type="button" id="clear_favorites" class="gitsearch-button-defaut">' . esc_html__('limpar favoritos', 'gitsearch') . '</button>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        endif;
        $html .= '<div class="gitsearch-col-8">';

        //API REPOSITÓRIOS
        if (isset($_GET['gitSearchRepository'])) :

            //Isso é para pegar o valor sem espaços para api entender a busca
            $gitSearchRepository = str_replace(' ', '%20', $_GET['gitSearchRepository']);
            //tenta pegar a paginação, caso não exista vai pra pagina 1
            $page = (isset($_GET['gitSearchPageNumber'])) ? $_GET['gitSearchPageNumber'] : 1;

            $curl = curl_init();

            $httpheader = [
                "Authorization: Bearer " . get_option('gitsearchtoken') . "",
                "Accept: application/vnd.github.v3+json",
                "Content-Type: text/plain",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 YaBrowser/16.3.0.7146 Yowser/2.5 Safari/537.36"
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.github.com/search/repositories?q=" . $gitSearchRepository . "&per_page=15&page=" . $page,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => $httpheader,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
            ]);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, true);
            }

            $total_count = $response['total_count'];

            //o número de paginas é o total dividido pelo numero de items por pagina
            $page_max = $total_count / 15;

            //LISTA DE REPOSITÓRIOS
            if ($response['items']) :

                $html .= '<div class="gitsearch-results">';
                $html .= '<h3 class="gitsearch-total-results">' . esc_html__('Resultados:', 'gitsearch') . $total_count;
                '</h3>';
                $html .= '<ul class="gitsearch-results-list">';
                foreach ($response['items'] as $item) :
                    $html .= '<li class="gitsearch-repos-item">';
                    $html .= '<div class="gitsearch-results-img" style="background-image: url(' . $item['owner']['avatar_url'] . ');"></div>';
                    $html .= '<a href="' . get_permalink() . '?gitSearchOwner=' . $item['owner']['login'] . '&gitSearchRepo=' . $item['name'] . '">';
                    $html .= '<h4>' . $item['full_name'] . '</h4>';
                    if ($item['description']) :
                        $html .= '<p class="gitsearch-description">' . $item['description'] . '</p>';
                    endif;
                    if ($item['language']) :
                        $html .= '<span class="gitsearch-language-list">' . $item['language'] . '</span>';
                    endif;
                    $html .= '</a>';
                    $html .= '<div class="gitsearch-favorite gitsearch-favorite-item" data-repository-id="' . $item['id'] . '" data-repository-name="' . $item['full_name'] . '" data-repository-url="' . get_permalink() . '?gitSearchOwner=' . $item['owner']['login'] . '&gitSearchRepo=' . $item['name'] . '">';
                    $html .= '<div class="gitsearch-bg">';
                    $html .= '<div class="gitsearch-favorite-selected">';
                    $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 4.248c-3.148-5.402-12-3.825-12 2.944 0 4.661 5.571 9.427 12 15.808 6.43-6.381 12-11.147 12-15.808 0-6.792-8.875-8.306-12-2.944z" /></svg>';
                    $html .= '</div>';
                    $html .= '<div class="gitsearch-favorite-unselected selected"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 9.229c.234-1.12 1.547-6.229 5.382-6.229 2.22 0 4.618 1.551 4.618 5.003 0 3.907-3.627 8.47-10 12.629-6.373-4.159-10-8.722-10-12.629 0-3.484 2.369-5.005 4.577-5.005 3.923 0 5.145 5.126 5.423 6.231zm-12-1.226c0 4.068 3.06 9.481 12 14.997 8.94-5.516 12-10.929 12-14.997 0-7.962-9.648-9.028-12-3.737-2.338-5.262-12-4.27-12 3.737z" /></svg>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</li>';
                endforeach;
                $html .= '</ul>';
                $html .= '<ul class="gitsearch-pagination">';
                if ($page != 1) :
                    $prev = $page - 1;
                    $html .= '<li><a href="' . get_permalink() . '?gitSearchRepository=' . str_replace(' ', '%20',  $gitSearchRepository) . '&gitSearchPageNumber=' . $prev . '">'
                        . esc_html__('<< Anterior', 'gitsearch') . '</a>';
                    $html .= '</li>';
                endif;

                if ($page) :
                    $html .= '<li class="gitsearch-current-page">' . $page . ' de ' . intval($page_max) . '</li>';
                endif;

                if ($page <= $page_max) :
                    $next = $page + 1;
                    $html .= '<li><a href="' . get_permalink() . '?gitSearchRepository=' . str_replace(' ', '%20',  $gitSearchRepository) . '&gitSearchPageNumber=' . $next . '">' . esc_html__('Próximo >>', 'gitsearch') . '</a></li>';
                endif;
                $html .= '</ul>';
                $html .= '</div>';
            endif;

        //API BUSCA O DONO DO REPOSITÓRIO SELECIONADO E SEU REPOSITÓRIO
        elseif (isset($_GET['gitSearchRepo']) && isset($_GET['gitSearchOwner'])) :

            $gitSearchRepo = str_replace(' ', '%20', $_GET['gitSearchRepo']);
            $gitSearchOwner = str_replace(' ', '%20', $_GET['gitSearchOwner']);

            $curl = curl_init();

            $httpheader = [
                "Authorization: Bearer " . get_option('gitsearchtoken') . "",
                "Accept: application/vnd.github.v3+json",
                "Content-Type: text/plain",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 YaBrowser/16.3.0.7146 Yowser/2.5 Safari/537.36"
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.github.com/repos/" . $gitSearchOwner . "/" . $gitSearchRepo . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => $httpheader,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
            ]);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, true);
            }


            $html .= '<div class="gitsearch-current-repo">';
            $html .= '<div class="gitsearch-current-repo-avatar">';
            $html .= '<img src="' . $response['owner']['avatar_url'] . '" alt="">';
            $html .= '</div>';
            $html .= '<div class="gitsearch-current-repo-content">';
            $html .= '<h1>' . $response['full_name'] . '</h1>';

            if ($response['created_at']) :
                $created_at = new DateTime($response['created_at']);
            endif;
            if ($response['updated_at']) :
                $updated_at = new DateTime($response['updated_at']);
            endif;

            if ($created_at) :
                $html .= '<p class="gitsearch-current-repo-dates">Criado: <strong>' . $created_at->format('d/m/Y') . '</strong>, Ultima Alteração: <strong> ' . $updated_at->format('d/m/Y') . '</strong></p>';
            endif;
            $html .= '<p>' . $response['description'] . '</p>';

            $html .= '<a href="' . $response['html_url'] . '" target="_blank">';
            $html .= '<div><strong>' . esc_html__('Ir para o diretório', 'gitsearch') . '</strong></div>';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1 15.889v-2.223s-3.78-.114-7 3.333c1.513-6.587 7-7.778 7-7.778v-2.221l5 4.425-5 4.464z" /></svg>';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '<div id="gitsearch-favorite-current-repo" class="gitsearch-favorite gitsearch-favorite-item" data-repository-id="' . $response['id'] . '" data-repository-name="' . $response['full_name'] . '" data-repository-url="' . get_permalink() . '?gitSearchOwner=' . $response['owner']['login'] . '&gitSearchRepo=' . $response['name'] . '">';
            $html .= '<div class="gitsearch-bg">';
            $html .= '<div class="gitsearch-favorite-selected">';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 4.248c-3.148-5.402-12-3.825-12 2.944 0 4.661 5.571 9.427 12 15.808 6.43-6.381 12-11.147 12-15.808 0-6.792-8.875-8.306-12-2.944z" /></svg>';
            $html .= '</div>';
            $html .= '<div class="gitsearch-favorite-unselected selected"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 9.229c.234-1.12 1.547-6.229 5.382-6.229 2.22 0 4.618 1.551 4.618 5.003 0 3.907-3.627 8.47-10 12.629-6.373-4.159-10-8.722-10-12.629 0-3.484 2.369-5.005 4.577-5.005 3.923 0 5.145 5.126 5.423 6.231zm-12-1.226c0 4.068 3.06 9.481 12 14.997 8.94-5.516 12-10.929 12-14.997 0-7.962-9.648-9.028-12-3.737-2.338-5.262-12-4.27-12 3.737z" /></svg>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            //API BUSCA REPOSITORIOS DO DONO DO REPOSITÓRIO SELECIONADO

            $curl = curl_init();

            $httpheader = [
                "Authorization: Bearer " . get_option('gitsearchtoken') . "",
                "Accept: application/vnd.github.v3+json",
                "Content-Type: text/plain",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 YaBrowser/16.3.0.7146 Yowser/2.5 Safari/537.36"
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.github.com/users/" . $gitSearchOwner . "/repos",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => $httpheader,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
            ]);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response, true);
            }

            if ($response) :
                $html .= '<div class="gitsearch-results">';
                $html .= '<h3 class="gitsearch-total-results">' . esc_html('Repositórios do proprietário', 'gitsearch') . '</h3>';
                $html .= '<ul class="gitsearch-results-list">';
                foreach ($response as $item) :
                    $html .= '<li class="gitsearch-repos-item">';
                    $html .= '<a href="' . get_permalink() . '?gitSearchOwner=' . $item['owner']['login'] . '&gitSearchRepo=' . $item['name'] . '">';
                    $html .= '<h4>' . $item['full_name'] . '</h4>';
                    if ($item['description']) :
                        $html .= '<p class="gitsearch-description">' . $item['description'] . '</p>';
                    endif;
                    if ($item['language']) :
                        $html .= '<span class="gitsearch-language-list">' . $item['language'] . '</span>';
                    endif;
                    $html .= '</a>';
                    $html .= '<div class="gitsearch-favorite gitsearch-favorite-item" data-repository-id="' . $item['id'] . '" data-repository-name="' . $item['full_name'] . '" data-repository-url="' . get_permalink() . '?gitSearchOwner=' . $item['owner']['login'] . '&gitSearchRepo=' . $item['name'] . '">';
                    $html .= '<div class="gitsearch-bg">';
                    $html .= '<div class="gitsearch-favorite-selected">';
                    $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 4.248c-3.148-5.402-12-3.825-12 2.944 0 4.661 5.571 9.427 12 15.808 6.43-6.381 12-11.147 12-15.808 0-6.792-8.875-8.306-12-2.944z" /></svg>';
                    $html .= '</div>';
                    $html .= '<div class="gitsearch-favorite-unselected selected"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 9.229c.234-1.12 1.547-6.229 5.382-6.229 2.22 0 4.618 1.551 4.618 5.003 0 3.907-3.627 8.47-10 12.629-6.373-4.159-10-8.722-10-12.629 0-3.484 2.369-5.005 4.577-5.005 3.923 0 5.145 5.126 5.423 6.231zm-12-1.226c0 4.068 3.06 9.481 12 14.997 8.94-5.516 12-10.929 12-14.997 0-7.962-9.648-9.028-12-3.737-2.338-5.262-12-4.27-12 3.737z" /></svg>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</li>';
                endforeach;
                $html .= '</ul>';
                $html .= '</div>';
            endif;
        endif;

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';


        return $html;
    else :
        //CASO NÃO TENHAM CADASTRADO A CHAVE
        return 'Você precisa cadastrar seu token do Github em https://github.com/settings/tokens';
    endif;
}
