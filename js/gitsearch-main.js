
var current_url = location.protocol + '//' + location.host + location.pathname;


var httpheader = {
    "Authorization": "Bearer " + gitsearch_script_params.gitsearchtoken + ""
};

document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";

    /**
     * FUNCTIONS
     */


    // CrossBrowser event listener function
    function AddEvent(elem, type, handler) {
        if (elem.addEventListener) {
            elem.addEventListener(type, handler, false);
        } else {
            elem.attachEvent('on' + type, function () {
                handler.call(elem);
            });
        }
        return false;
    }

    // Getting data from local storage
    function getrepoData() {
        return JSON.parse(localStorage.getItem('favorites'));
    }

    // Writting data in local storage
    function setrepoData(o) {
        localStorage.setItem('favorites', JSON.stringify(o));
        return false;
    }

    // Counter of one type items in favorites
    function countRepoData(object) {
        var count = 0;
        for (var i in object) {
            count++;
        }

        return count;
    }

    //adiciona favoritos
    function addTofavorites(e, counter) {
        // this.disabled = true;
        var repoData = getrepoData() || {},
            parentBox = this.parentNode.parentNode,
            itemRepoId = this.getAttribute('data-repository-id'),
            itemRepoName = this.getAttribute('data-repository-name'),
            favoritesCont = document.getElementById('gitsearch-favorites-content'),
            itemRepoUrl = this.getAttribute('data-repository-url');

        repoData[itemRepoId] = [itemRepoName, itemRepoUrl];


        if (!setrepoData(repoData)) {
            //Updating data in local storage
            //this.disabled = false; //unblock button "in favorites"
        }

        //alert('Repository "' + itemRepoName + '" added in your favorites');
        var gitsearch_alert = document.getElementById('gitsearch-alert');
        if (gitsearch_alert) {
            gitsearch_alert.remove();
        }

        var gitsearch_favorites = document.getElementById('gitsearch-favorites');
        var alert = '<span id="gitsearch-alert">Repositório ' + itemRepoName + ' adicionado aos favoritos</span>';
        gitsearch_favorites.insertAdjacentHTML('beforeend', alert);
        var gitsearch_alert = document.getElementById('gitsearch-alert');
        //gitsearch_alert.style.opacity = '1';
        setTimeout(function () {
            gitsearch_alert.style.bottom = '30px';
            setTimeout(function () {
                gitsearch_alert.style.opacity = '0';
                setTimeout(function () {
                    gitsearch_alert.remove();
                }, 1000);
            }, 5000);
        }, 30);


        setTimeout(favoritesCount(), 10);
        openfavorites(favoritesCont);

        updateBtnRemove();

        return false;
    }

    //remove o favorito selecionado
    function removeFromRepoData(e, counter) {
        //e.disabled = true;
        var repoData = getrepoData(),
            parentBox = e.parentNode.parentNode,
            favoritesCont = document.getElementById('gitsearch-favorites-content'),
            itemRepoId = e.getAttribute('data-repository-id');

        if (repoData != null) {
            if (repoData.hasOwnProperty(itemRepoId)) {
                delete repoData[itemRepoId];
                //parentBox.remove(); - remove all
            }
        }

        if (countRepoData(repoData) == 0) {
            //window.location.reload(); - load page
            localStorage.removeItem('favorites');
            var results_favorite_item = document.getElementsByClassName('gitsearch-favorite-item');
            for (var i = 0; i < results_favorite_item.length; i++) {
                var item = results_favorite_item[i];
                item.querySelectorAll('.gitsearch-favorite-selected')[0].classList.remove("selected");
                item.querySelectorAll('.gitsearch-favorite-unselected')[0].classList.add("selected");
            }
        } else {
            if (!setrepoData(repoData)) {
                //this.disabled = false;
            }
        }

        var results_favorite_item = document.getElementsByClassName('gitsearch-favorite-item');
        for (var i = 0; i < results_favorite_item.length; i++) {
            var item = results_favorite_item[i];
            for (var items in repoData) {
                if (item.getAttribute('data-repository-id') != items) {
                    item.querySelectorAll('.gitsearch-favorite-selected')[0].classList.remove("selected");
                    item.querySelectorAll('.gitsearch-favorite-unselected')[0].classList.add("selected");
                }
            }
        }

        setTimeout(favoritesCount(), 10);
        openfavorites(favoritesCont);
        updateBtnRemove();

        return false;
    }

    //faz a contagem dos favoritos
    function favoritesCount(e) {
        var repoData = getrepoData(),
            totalItems = 0,
            countElement = document.getElementById('favoritesCounter');

        if (repoData !== null) {
            for (var items in repoData) {
                for (var i = 0; i < repoData[items].length; i++) {
                    if (i == 1) {
                        totalItems = totalItems + 1;
                    }
                }
            }
        }

        updateBtnRemove();
        return countElement.innerHTML = totalItems;
    }

    //lista os favoritos
    function openfavorites(favorites) {

        var repoData = getrepoData(),
            totalItems = '',
            favoritesCont = favorites,
            results_favorite_item = document.getElementsByClassName('gitsearch-favorite-item');

        favoritesCont.innerHTML = '';

        if (repoData !== null) {
            for (var items in repoData) {

                totalItems += '<li class="item-id-' + items + '"><a href="' + repoData[items][1] + '"><h4>' + repoData[items][0] + '</h4></a><button class="gitsearch-btn-remove" data-repository-id="' + items + '">X</button></li>';

            }

            for (var i = 0; i < results_favorite_item.length; i++) {
                var item = results_favorite_item[i];
                for (var items in repoData) {
                    if (item.getAttribute('data-repository-id') == items) {
                        item.querySelectorAll('.gitsearch-favorite-unselected')[0].classList.remove("selected");
                        item.querySelectorAll('.gitsearch-favorite-selected')[0].classList.add("selected");
                    }
                }
            }

            favoritesCont.innerHTML = totalItems;
        } else {

            for (var i = 0; i < results_favorite_item.length; i++) {
                var item = results_favorite_item[i];
                item.querySelectorAll('.gitsearch-favorite-selected')[0].classList.remove("selected");
                item.querySelectorAll('.gitsearch-favorite-unselected')[0].classList.add("selected");
            }

            favoritesCont.innerHTML = '<div class="alert alert-warning">nenhum favorito.</div>';
        }


        updateBtnRemove();
        return false;
    }

    //função parar atualizar os eventos de click nos botões remover, isso se faz necessário, pois ao atualiar o html com o DOM, se perde os eventos dos elementos anteriores
    function updateBtnRemove() {
        var gitsearchRemove = document.getElementsByClassName('gitsearch-btn-remove');

        for (var i = 0; i < gitsearchRemove.length; ++i) {
            var item = gitsearchRemove[i];
            AddEvent(item, 'click', function (e) {
                removeFromRepoData(this);

            });
        };
    }

    /**
     * API
     */

    //captura o valor do input para pesquisa
    var gitsearch_repositories = document.getElementById("gitsearch-repositories");

    //função api de sugestões na pesquisa
    function gitsearch_get_repositories() {

        if (gitsearch_repositories.value && gitsearch_repositories.value != '') {

            //Como são sugestões, decidi apresentar apenas 5, caso queria pode especificar melhor, ou clicar em ver mais, para ter mais resultados
            fetch("https://api.github.com/search/repositories?q=" + gitsearch_repositories.value + "&per_page=5&page=1", {
                "method": "GET",
                "headers": httpheader,
            })
                .then(function (response) {
                    return response.json();

                }).then(function (json) {
                    var content = '';
                    json.items.forEach((item) => {
                        content += '<li><a href="' + current_url + '?gitSearchOwner=' + item.owner.login + '&gitSearchRepo=' + item.name + '"><h4>' + item.full_name + '</h4><p class="gitsearch-description">' + item.description + '</p></a></li>';
                    });
                    content += '<li class="gitsearch-li-viewmore"><input id="gitsearch-viewmore" type="submit" value="ver mais..."></li>';
                    //esse css é para criar a animação de aparecer e sumir
                    document.getElementById('gitsearch-suggestions').style.height = 'auto';
                    document.getElementById('gitsearch-suggestions').style.padding = '15px';
                    document.getElementById('gitsearch-suggestions').style.opacity = '1';
                    document.getElementById('gitsearch-suggestions').innerHTML = content;
                })
                .catch(err => {
                    console.error(err);
                    //esse css é para criar a animação de aparecer e sumir
                    document.getElementById('gitsearch-suggestions').style.height = '0';
                    document.getElementById('gitsearch-suggestions').style.padding = '0px';
                    document.getElementById('gitsearch-suggestions').style.opacity = '0';
                    document.getElementById('gitsearch-suggestions').innerHTML = '';

                });
        } else {
            //esse css é para criar a animação de aparecer e sumir
            document.getElementById('gitsearch-suggestions').style.height = '0';
            document.getElementById('gitsearch-suggestions').style.padding = '0px';
            document.getElementById('gitsearch-suggestions').style.opacity = '0';
            document.getElementById('gitsearch-suggestions').innerHTML = '';
            document.getElementById('gitsearch-suggestions').innerHTML = '';
        }
    }

    //chama a função da api para sugestões quando se interage com o campo
    gitsearch_repositories.onchange = gitsearch_get_repositories;
    gitsearch_repositories.onkeyup = gitsearch_get_repositories;
    gitsearch_repositories.onfocusout = gitsearch_get_repositories;


    addEventListener("keyup", function (event) {

        keyPress(event);
    });

    //adiciona o evento para o esc atualizar a listagem de sugestões, isso resolve caso você saia do campo e mesmo com ele vazio ainda apareça a ultima pesquisa.
    function keyPress(e) {
        e.preventDefault();
        if (e.key === "Escape" || e.key === "Esc") {
            gitsearch_get_repositories();
        } else {
            return false;
        }
    }

    /**
   * ONLOAD
   */
    //capturando os elementos usados
    var gitsearch_repos_item = document.querySelectorAll('.gitsearch-repos-item'),
        favoritesCont = document.getElementById('gitsearch-favorites-content'),
        favoriteCurrentRepo = document.getElementById('gitsearch-favorite-current-repo'),
        clearfavorites = document.getElementById('clear_favorites'),
        button = document.getElementById('openfavorites');

    //adiciona o evento de favoritar na interna do repositório selecionado
    if (favoriteCurrentRepo) {
        AddEvent(favoriteCurrentRepo, 'click', function () {
            AddEvent(favoriteCurrentRepo, 'click', addTofavorites);
        });
    }

    //adiciona o evento de favoritar nos repositórios listados
    for (var i = 0; i < gitsearch_repos_item.length; i++) {
        AddEvent(gitsearch_repos_item[i].querySelector('.gitsearch-favorite-item'), 'click', addTofavorites);
    }

    //Atualiza a contatem de favoritos
    favoritesCount();

    //Caso o elemento exista, lista os favoritos
    if (favoritesCont) {
        openfavorites(favoritesCont);
    }

    //adiciona o evento atualizar os favoritos
    if (button) {
        AddEvent(button, 'click', function () {
            openfavorites(favoritesCont);
        });
    }

    //adiciona eventos parar o botão limpar favoritos
    if (clearfavorites) {
        AddEvent(clearfavorites, 'click', function (e) {
            localStorage.removeItem('favorites');
            setTimeout(favoritesCount(), 10);
            openfavorites(favoritesCont);
        });
    }

    //Atualiza a os eventos dos botões remover
    updateBtnRemove();


});