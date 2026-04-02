<?php

return [

    'page' => [
        'title' => 'Gestione File',
        'navigation_label' => 'Gestione File',
    ],

    'toolbar' => [
        'upload' => 'Carica',
        'new_folder' => 'Nuova cartella',
        'refresh' => 'Aggiorna',
        'deselect' => 'Deseleziona',
        'sort_name' => 'Nome',
        'sort_size' => 'Dimensione',
        'sort_date' => 'Data',
        'sort_type' => 'Tipo',
        'sort_asc' => 'Ordine crescente',
        'sort_desc' => 'Ordine decrescente',
        'selected_count' => ':count elemento selezionato|:count elementi selezionati',
    ],

    'actions' => [
        'rename' => 'Rinomina',
        'delete' => 'Elimina',
        'preview' => 'Anteprima',
        'full_preview' => 'Anteprima completa',
        'download' => 'Scarica',
        'open' => 'Apri',
        'upload_files' => 'Carica file',
        'new_folder' => 'Nuova cartella',
        'move_selected' => 'Sposta selezionati',
        'delete_selected' => 'Elimina selezionati',
        'confirm_selection' => 'Conferma selezione',
        'browse' => 'Sfoglia',
        'select_file' => 'Seleziona file',
        'check_updates' => 'Controlla aggiornamenti',
        'update_available_title' => 'Aggiornamento disponibile',
        'update_available_body' => 'La versione :latest è disponibile (attuale: :current).',
        'up_to_date_title' => 'Aggiornato',
        'up_to_date_body' => 'Stai usando la versione più recente (:current).',
        'update_check_failed_title' => 'Verifica fallita',
        'update_check_failed_body' => 'Impossibile verificare gli aggiornamenti. Riprova più tardi.',
    ],

    'messages' => [
        'files_uploaded' => ':count file caricato|:count file caricati',
        'some_files_not_uploaded' => 'Alcuni file non sono stati caricati',
        'folder_created' => "Cartella ':name' creata",
        'renamed_successfully' => 'Rinominato con successo',
        'item_deleted' => "':name' eliminato",
        'items_deleted' => ':count elementi eliminati',
        'items_moved' => ':count elementi spostati',
        'item_moved' => "':name' spostato in ':destination'",
        'move_error' => 'Errore durante lo spostamento',
        'file_too_large' => 'Il file non può superare :max.',
        'upload_failed' => 'Caricamento fallito. Il file non può superare :max.',
        'file_type_not_allowed' => 'Tipo di file non consentito: :name',
        'folder_already_exists' => "La cartella ':name' esiste già.",
        'name_already_exists' => "':name' esiste già.",
        'extension_changed' => "Cambiare l'estensione del file potrebbe renderlo inutilizzabile.",
        'permission_denied' => 'Non hai i permessi per eseguire questa azione.',
    ],

    'labels' => [
        'folder_name' => 'Nome cartella',
        'new_name' => 'Nuovo nome',
        'destination_folder' => 'Cartella di destinazione',
        'destination_placeholder' => 'es. images/photos',
        'destination_helper' => 'Lascia vuoto per spostare nella root.',
        'file' => 'File',
        'name_validation' => 'Il nome non può contenere / o \\.',
        'folder' => 'Cartella',
        'files_count' => ':count file|:count file',
        'folders_count' => ':count cartella|:count cartelle',
        'selected' => ':count selezionati',
        'file_selected' => '1 file selezionato',
        'no_file_selected' => 'Nessun file selezionato',
        'root' => 'Root',
        'showing_of_total' => ':shown di :total file',
        'loading_more' => 'Caricamento...',
    ],

    'modals' => [
        'confirm_deletion' => 'Conferma eliminazione',
        'deletion_warning' => 'Questa azione non può essere annullata. Vuoi procedere?',
        'bulk_deletion_warning' => 'Stai per eliminare :count elementi. Questa azione non può essere annullata.',
        'close' => 'Chiudi',
    ],

    'sidebar' => [
        'folders' => 'Cartelle',
        'expand' => 'Espandi',
        'collapse' => 'Comprimi',
        'empty' => '(vuota)',
        'show_folders' => 'Mostra cartelle',
        'show_preview' => 'Mostra anteprima',
        'hide_preview' => 'Nascondi anteprima',
    ],

    'file_types' => [
        'image' => 'Immagine',
        'document' => 'Documento',
        'audio' => 'Audio',
        'video' => 'Video',
        'archive' => 'Archivio',
        'code' => 'Codice',
        'other' => 'Altro',
    ],

    'editors' => [
        'file_manager' => 'Gestione File',
        'insert' => 'Inserisci',
        'select_files' => 'Seleziona i file da inserire',
        'no_url_warning' => 'Alcuni file sono stati ignorati perché non hanno un URL pubblico.',
    ],

    'commands' => [
        'thumbnails_cleared' => 'Miniature eliminate con successo dal disco [:disk].',
        'no_thumbnails' => 'Nessuna cartella miniature trovata sul disco [:disk].',
        'remote_disk_not_supported' => 'Il disco [:disk] usa il driver [:driver]. I dischi remoti sono disponibili nella versione Pro.',
    ],

    'misc' => [
        'empty_folder' => 'Questa cartella è vuota',
        'empty_folder_hint' => 'Carica file o crea una nuova cartella per iniziare',
        'select_file_preview' => 'Seleziona un file per visualizzare i dettagli',
        'info' => 'Informazioni',
        'type' => 'Tipo',
        'mime' => 'MIME',
        'size' => 'Dimensione',
        'modified' => 'Modificato',
        'audio_not_supported' => 'Il tuo browser non supporta l\'elemento audio.',
        'video_not_supported' => 'Il tuo browser non supporta l\'elemento video.',
        'cannot_read_file' => 'Impossibile leggere il contenuto del file.',
    ],

];
