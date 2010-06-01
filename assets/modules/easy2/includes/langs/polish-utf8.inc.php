<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$e2g_lang['polish-utf8'] = array (
//        'charset' => 'ISO-8859-1',
        'charset' => 'UTF-8',
        'create_dir' => 'Nowy folder',
        'manager' => 'Pliki',
        'upload' => 'Ładowanie plików',
        'help' => 'Pomoc',
        'install' => 'Instalacja',
        'upload_dir' => 'Załaduj zdjęcia do folderu',
        'file' => 'Plik',
        'file2' => 'pliku',
        'name' => 'Nazwa',
        'description' => 'Opis',
        'upload_btn' => 'Załaduj',
        'add_field_btn' => 'Załaduj więcej',
        'remove_field_btn' => 'Usuń',
        'js_delete_confirm' => 'Czy na pewno chcesz usunąć te pliki?\n\nKliknij OK aby potwierdzić.',
        'js_delete_folder_confirm' => 'Czy na pewno chcesz usunąć ten folder?\nWszystkie pliki i foldery znajdujące się w tym folderze zostaną usunięte.\n\nKliknij OK aby potwierdzić.',
        'js_ignore_ip_address_confirm' => 'Czy na pewno chcesz ignorować ten adres IP?\nWszystkie komentarze z tego adresu IP będą również ukryte.\n\nKliknij OK aby potwierdzić.',
        'js_unignore_ip_address_confirm' => 'Czy na pewno chcesz zaprzestać ignorowania tego adresu IP?\nWszystkie komentarze z tego adresu IP będą ponownie wyświetlane.\n\nKliknij OK aby potwierdzić.',
        'enter_dirname' => 'Wpisz nazwę folderu',
        'enter_new_dirname' => 'Zmień nazwę folderu',
        'badchars' => 'Nieprawidłowe znaki dla używanego systemu plików.',
		'char_limitation' => 'Nazwy plików w archiwum ZIP muszą się składać wyłącznie ze standardowych znaków łacińskich (bez znaków diakrytycznych) w przeciwnym wypadku wystąpią błędy podczas jego ładowania.<br />Znaki kodowane w UTF-8 będą zignorowane lub zastąpione przez najbliższy im znak standardowy.',
		'zip_foldername' => 'Nazwa archiwum ZIP będzie wykorzystana jako nazwa dla nowego folderu.<br />
            Upewnij się, że w aktualnym folderze nie znajduje się już folder o takiej nazwie, gdyż w przeciwnym wypadku zostanie on nadpisany.<br />
			Nazwa ta będzie również wykorzystywana jako adres URL.',
        'debug' => 'Opcje debugowania',
        'dir' => 'folder',
        'tinymcefolder' => 'folder edytora TinyMCE',
        'thumb' => 'miniaturka',
        'empty' => 'pusty',
        'valid_extensions' => 'Akceptowane rozszerzenia to:',
        'files_uploaded' => 'pliki załadowane pomyślnie',
        'directory_created' => 'folder utworzony pomyślnie',
        'files_deleted' => 'pliki usunięte pomyślnie',
        'files_deleted_fdb' => 'pliki pomyślnie usunięte z bazy danych',
        'files_deleted_fhdd' => 'pliki pomyślnie usunięte z dysku',
        'dirs_deleted' => 'foldery pomyślnie usunięte',
        'dirs_deleted_fdb' => 'foldery pomyślnie usunięte z bazy danych',
        'dirs_deleted_fhdd' => 'foldery pomyślnie usunięte z dysku',
        'dir_delete' => 'folder usunięty',
        'dir_delete_fdb' => 'folder usunięty wyłącznie z bazy danych',
        'dir_delete_fhdd' => 'folder usunięty wyłącznie z dysku',
        'file_delete' => 'plik usunięty',
        'file_delete_fdb' => 'plik usunięty wyłącznie z bazy danych',
        'file_delete_fhdd' => 'plik usunięty wyłącznie z dysku',

        'dir_edded' => 'Folder utworzony pomyślnie',
        'dir_edd_err' => 'Wystąpił błąd podczas próby utworzenia folderu',

        'synchro' => 'Synchronizuj',
        'synchro_suc' => 'Galeria została pomyślnie zsynchronizowana',
        'synchro_err' => 'Wystąpił błąd podczas próby synchronizacji galerii',

        'indexfile' => '<h2>Brak dostępu</h2>Nie posiadasz praw dostępu do tego folderu',

        'restore_suc' => 'Nazwa galerii została przywrócona',
        'restore_err' => 'Wystąpił błąd podczas próby przywracania',

        'archive' => 'Archiwum ZIP',

        'commentsmgr' => 'Zarządzanie komentarzami',
        'allcomments' => 'Wszystkie komentarze',
        'comments' => 'Komentarze',
        'author' => 'Autor',
        'date' => 'Data',
        'ipaddress' => 'Adres IP',
        'ignored_ip' => 'Ignorowane adresy IP',
        'hiddencomments' => 'Ukryte komentarze',
        'ignore' => 'Ignoruj',
        'unignore' => 'Usuń z ignorowanych',
        'modified' => 'Zmodyfikowano',
        'withselected' => 'Zaznaczone',
        'delete' => 'Usuń',
        'movetofolder' => 'Przenieś do folderu',
        'move' => 'Przenieś',
        'refresh' => 'Odśwież',
        'info' => 'Właściwości',
        'size' => 'Rozmiar',
        'options' => 'Akcje',
        'edit' => 'Edytuj',
        'editing' => 'Edytuj właściwości',
        'add_to_db' => 'Dodaj do bazy danych',
        'path' => 'Ścieżka',
        'updated' => 'uaktualniono',
        'back_to_fmanager' => 'Powrót',
        'save' => 'Zapisz',
        'cancel' => 'Anuluj',
        'clean_cache' => 'Wyczyść cache',
        'cache_clean' => 'Cache został wyczyszczony',
        'cache_clean_err' => 'Wystąpił błąd podczas próby wyczyszczenia cache\'u',

        'config' => 'Konfiguracja',

        'newimgcfg' => 'Ustawienia obrazów',
        'thumbscfg' => 'Ustawienia miniaturek',
        'thumbcnt' => 'Ustawienia wyświetlania',
        'w' => 'Szerokość',
        'h' => 'Wysokość',
        'thq' => 'Stopień kompresji',
        'resize_type' => 'Rodzaj skalowania miniaturek',
        'inner' => 'przycięcie',
        'shrink' => 'skalowanie',
        'resize' => 'skalowanie proporcjonalne',
        'thbg_rgb' => 'Kolor tła miniaturki',

        'name_len' => 'maksymalna długość nazwy miniaturki',
        'cat_name_len' => 'maksymalna długość nazwy folderu',
        'colls' => 'Ilość komumn',
        'notables' => 'Układ',
        'limit' => 'Ilość obrazów na stronę',
        'glib' => 'Biblioteka JS',
        'ecl' => 'Ilość komentarzy na stronę',
        'tpl' => 'Szablony',
        'css' => 'CSS',
        'gallery' => 'Galeria',
        'comments_row' => 'Wiersz komentarza',
        'wm' => 'Znaki wodne',
        'type' => 'typ',
        'text' => 'tekst',
        'image' => 'obrazek',
        'wmt' => 'tekst/ścieżka',
        'wmpos1' => 'Pozycja w poziomie',
        'wmpos2' => 'Pozycja w pionie',
        'pos1' => 'lewo',
        'pos2' => 'środek',
        'pos3' => 'prawo',
        'pos4' => 'góra',
        'pos5' => 'środek',
        'pos6' => 'dół',
        'order' => 'Sortuj miniaturki wg',
        'order2' => 'Sortuj foldery wg',
        'date_added' => 'data',
        'filename' => 'nazwa pliku',
        'last_modified' => 'data edycji',
        'comments_cnt' => 'liczba komentarzy',
        'random' => 'losowo',
        'captcha' => 'Captcha',

        'asc' => 'rosnąco',
        'desc' => 'malejąco',

        'on' => 'Włączone',
        'off' => 'Wyłączone',

        'cfg_com0' => 'Tryb debugowania.',
        'cfg_com1' => 'Ścieżka do folderu <b class="warning">zakończona slashem</b>, np.: assets/easy2gallery/.',
        'cfg_com1a' => 'Folder w którym znajduje się edytor TinyMCE, np.: tinymce3241 (MODx Evolution 1.0.2)',
        'cfg_com2' => 'Maksymalna szerokość w pikselach. Większe obrazy będą automatycznie skalowane.<br /> <b>0 - brak limitu</b>.',
        'cfg_com3' => 'Maksymalna wysokość w pikselach. Większe obrazy będą automatycznie skalowane.<br /> <b>0 - brak limitu</b>.',
        'cfg_com4' => 'Stopień kompresji jpeg od 0 do 100%.<br><b class="warning">Wyłącznie dla obrazów o rozmiarach większych niż ustalone maksimum.</b>.',
        'cfg_com5' => 'Szerokość miniaturek w pikselach (px)',
        'cfg_com6' => 'Wysokość miniaturek w pikselach (px)',
        'cfg_com6a' => '<b>Przycięcie:</b> obraz zostaje przycięty do wymiarów miniaturki.<br /><b>Skalowanie:</b> obraz zostaje zeskalowany do rozmiarów miniaturki bez uwzględniania proporcji.<br /><b>Skalowanie proporcjonalne:</b> obraz zostaje zeskalowany do rozmiarów miniaturki z uwzględnieniem jego proporcji. Ewentualny naddatek zostaje wypełniony kolorem ustalonym poniżej.',
        'cfg_com6b' => 'Biały to 255 255 255, czarny 0 0 0.',
        'cfg_com7' => 'Stopień kompresji jpeg dla miniaturek od 0 do 100%.',
        'cfg_com8' => 'Ilość kolumn w których wyświetlane są miniaturki.',
        'cfg_com9' => 'Ilość miniaturek wyświetlanych na poszczególnych stronach.',
        'cfg_com10' => 'Biblioteka graficzna',
        'cfg_com10a' => 'Wykorzystywanie tabel do wyświetlania miniaturek albo nie.',
        'cfg_com11' => 'Ilość komentarzy wyświetlanych na poszczególnych stronach.',
        'cfg_com12' => 'Typ znaku wodnego. Tekst albo obrazek.',
        'cfg_com13' => 'Tekst dla znaku wodnego lub ścieżka do obrazu znaku wodnego.',
        'cfg_com14' => 'Pozycja znaku wodnego w poziomie.',
        'cfg_com15' => 'Pozycja znaku wodnego w pionie.',
        'cfg_com16' => 'Metoda sortowania plików',
        'cfg_com17' => 'Nazwa "chunka" albo ścieżka do pliku z szablonem.',
        'cfg_com18' => 'Maksymalna długość nazwy pliku',
        'cfg_com19' => 'Nazwa "chunka" albo ścieżka do pliku, <b class="warning">relatywna do comments.easy2gallery.php</b>',
        'cfg_com20' => 'Maksymalna długość nazwy folderu',
        'cfg_com21' => 'Metoda sortowania folderów',

        'add_comment' => 'Dodaj komentarz',
        'username' => 'Podpis',
        'useremail' => 'Email',
        'userecomment' => 'Komentarz',
        'send_btn' => 'Wyślij',
        'empty_name_comment' => 'Musisz wpisać swój podpis oraz komentarz.',
        'comment_added' => 'Komentarz został dodany.',
        'comment_add_err' => 'Wystąpił błąd podczas próby dodania komentarza.',

        'file_added' => 'Plik został dodany.',
        'ip_ignored_suc' => 'To IP jest od teraz ignorowane.',
        'ip_ignored_err' => 'Nie można dodać IP do listy ignorowanych.',
        'ip_unignored_suc' => 'To IP już nie jest ignorowane.',
        'ip_unignored_err' => 'Nie można usunąć IP z listy ignorowanych.',

        '_thumb_err' => 'Nie można utworzyć folderu &quot;_thumbnails&quot;.',
        'upload_err' => 'Nie można załadować pliku.',
        'add_file_err' => 'Nie można dodać pliku.',
        'ren_file_err' => 'Nie można zmienić nazwy pliku.',
        'type_err' => 'Niedozwolony typ pliku.',
        'db_err' => 'Błąd bazy danych.',
        'directory_create_err' => 'Nie można utworzyć folderu',
        'files_delete_err' => 'Nie można usunąć plików.',
        'dirs_delete_err' => 'Nie można usunąć folderów.',
        'dir_delete_err' => 'Nie można usunąć folderu.',
        'dpath_err' => 'ścieżka lub id folderu jest niezdefiniowane',
        'fpath_err' => 'ścieżka lub id obrazu jest niezdefiniowane',
        'file_delete_err' => 'Nie można usunąć pliku',
        'id_err' => 'nieprawidłowe id',
        'update_err' => 'zmiany nie zostały zapisane',
        'renamefile' => 'Zmień nazwę pliku',

        'uim_preview' => 'Podgląd',
        'uim_preview_err' => 'Podgląd<br />niedostępny',


        'paramhelptitle' => 'Parametry',
        'tplhelptitle' => 'Szablony',
        'moreinfotitle' => 'Więcej informacji',

        'paramhelpcontent' => '',
        'tplhelpcontent' => '<h2> Parametry szablonów </h2>
<p><strong>glib</strong> - biblioteka JavaScript.<br />
Wartość domyślna: highslide
</p>
<p><strong>css</strong> - style CSS.<br />
Nazwa "chunka" albo ścieżka do pliku z szablonem.<br />
Wartość domyślna: assets/modules/easy2/templates/style.css
</p>
<p><strong>tpl</strong> - szablon galerii.<br />
Nazwa "chunka" albo ścieżka do pliku z szablonem.<br />
Wartość domyślna: assets/modules/easy2/templates/gallery.htm
</p>
<p><strong>dir_tpl</strong> - szablon folderu.<br />
Nazwa "chunka" albo ścieżka do pliku z szablonem.<br />
Wartość domyślna: assets/modules/easy2/templates/directory.htm
</p>
<p><strong>thumb_tpl</strong> - szablon miniaturki.<br />
Nazwa "chunka" albo ścieżka do pliku z szablonem.<br />
Wartość domyślna: assets/modules/easy2/templates/thumbnail.htm
</p>
<p><strong>rand_tpl</strong> - szablon dla obrazków wyświetlanych losowo<br />
Nazwa "chunka" albo ścieżka do pliku z szablonem.<br />
Wartość domyślna: assets/modules/easy2/templates/random_thumbnail.htm
</p>
<p><strong>comments_tpl</strong> - szablon dla komentarzy.<br />
Nazwa "chunka" albo ścieżka do pliku, relatywna do comments.easy2gallery.php.<br />
Wartość domyślna: assets/modules/easy2/templates/comments.htm
</p>
<p><strong>comments_row_tpl</strong> - szablon wiersza komentarza.<br />
Nazwa "chunka" albo ścieżka do pliku, relatywna do comments.easy2gallery.php.<br />
Wartość domyślna: assets/modules/easy2/templates/comments_row.htm
</p>
<p>&nbsp;</p>
<h2> Opis placeholderów </h2>
<h3>Galeria:</h3>
<p><strong>[+easy2:cat_name+]</strong> - nazwa aktualnego folderu<br />
<strong>[+easy2:back+]</strong> - link powrotu do poziomu bazowego (parent)<br />
<strong>[+easy2:content+]</strong> - zawartość galerii<br />
<strong>[+easy2:pages+]</strong> - paginacja</p>
<h3>Foldery:</h3>
<p><strong>[+easy2:cat_name+]</strong> - nazwa folderu<br />
<strong>[+easy2:cat_id+]</strong> - id folderu<br />
<strong>[+easy2:parent_id+]</strong> - id folderu w którym znajduje się bierzący folder<br />
<strong>[+easy2:cat_level+]</strong> - poziom zagnieżdżenia<br />
<strong>[+easy2:count+]</strong> - liczba plików
</p>
<h3> Miniaturki: </h3>
<p><strong>[+easy2:src+]</strong> - ścieżka do miniaturki<br />
<strong>[+easy2:w+]</strong> - szerokość miniaturki<br />
<strong>[+easy2:h+]</strong> - wysokość miniaturki<br />
<strong>[+easy2:id+]</strong> - id obrazka<br />
<strong>[+easy2:name+]</strong> - nazwa obrazka (jeśli > name_len to wtedy długość = name_len-2)<br />
<strong>[+easy2:title+]</strong> - nazwa obrazka (pełna nazwa)<br />
<strong>[+easy2:description+]</strong> - opis obrazka<br />
<strong>[+easy2:filename+]</strong> - nazwa pliku obrazka<br />
<strong>[+easy2:size+]</strong> - rozmiar pliku obrazka (bytes)<br />
<strong>[+easy2:comments+]</strong> - ilość komentarzy dla obrazka<br />
<strong>[+easy2:date_added+]</strong> - data dodania<br />
<strong>[+easy2:last_modified+]</strong> - data modyfikacji<br />
<strong>[+easy2:dir_id+]</strong> - id folderu</p>
<h3> Komentarze (wiersz): </h3>
<p><strong>[+easy2:id+]</strong> - id komentarza<br />
<strong>[+easy2:file_id+]</strong> - id pliku<br />
<strong>[+easy2:author+]</strong> - podpis autora komentarza<br />
<strong>[+easy2:email+]</strong> - email autora komentarza<br />
<strong>[+easy2:name_w_mail+]</strong> - jeśli email jest podany "&lt;a href="mailto:[+easy2:email+]"&gt;[+easy2:author+]&lt;/a&gt;", w przeciwnym wypadku "[+easy2:author+]"<br />
<strong>[+easy2:comment+]</strong> - treść komentarza<br />
<strong>[+easy2:date_added+]</strong> - data dodania komentarza<br />
<strong>[+easy2:last_modified+]</strong> - data modyfikacji komentarza</p>
<h3> Komentarze (formularz dodawania komentarza): </h3>
<p>
<strong>[+easy2:title+]</strong> - tytuł strony (zdefiniowany w pliku langs/*.comments.php)<br />
<strong>[+easy2:body+]</strong> - komentarze<br />
<strong>[+easy2:pages+]</strong> - linki paginacji<br />
+ ustawienia specyficzne dla danego języka: langs/*.comments.php</p>',
        'moreinfocontent' => '
<p><a href="http://e2g.info/documentation.htm" target="_blank"><b>Dokumentacja</b></a></p>
<p><b><a href="http://e2g.info/" target="_blank">Oficjalna strona Easy 2 Gallery</a></b></p>
<p><b><a href="http://wiki.modxcms.com/index.php/Easy2gallery" target="_blank">Easy 2 Gallery WIKI</a></b></p>

'
);

?>