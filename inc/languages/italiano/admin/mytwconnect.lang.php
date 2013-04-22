<?php
// installation
$l['mytwconnect'] = "MyTwitter Connect";
$l['mytwconnect_pluginlibrary_missing'] = "<a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> è assente. Installalo prima di utilizzare MyTwitter Connect.";

// settings
$l['mytwconnect_settings'] = "Login con Twitter";
$l['mytwconnect_settings_desc'] = "Qui puoi gestire le impostazioni per il login con Twitter, come inserire ID e secret token della tua applicazione.";
$l['mytwconnect_settings_enable'] = "Interruttore generale";
$l['mytwconnect_settings_enable_desc'] = "Desideri utilizzare il login con Twitter per i tuoi utenti? Questo interruttore generale ti aiuta a disattivare con un click tutto il sistema.";
$l['mytwconnect_settings_appid'] = "App ID";
$l['mytwconnect_settings_appid_desc'] = "Inserisci l'ID della tua applicazione creata su Twitter. Verrà utilizzata insieme alla secret token per ottenere i dati degli utenti.";
$l['mytwconnect_settings_appsecret'] = "App Secret";
$l['mytwconnect_settings_appsecret_desc'] = "Inserisci la secret token della tua applicazione creata su Twitter. Verrà utilizzata insieme all\'ID per ottenere i dati degli utenti.";
$l['mytwconnect_settings_usergroup'] = "Gruppo post-registrazione";
$l['mytwconnect_settings_usergroup_desc'] = "Inserisci il gruppo in cui verrà inserito un utente dopo la registrazione con Twitter (che avviene automaticamente cliccando il pulsante Login con Twitter). Di default è il 2, quello dedicato ai membri registrati.";
$l['mytwconnect_settings_fastregistration'] = "Registrazione one-click";
$l['mytwconnect_settings_fastregistration_desc'] = "Se quest'opzione è abilitata, la registrazione degli utenti attraverso Twitter sarà processata immediatamente senza chiedere all'utente un nome utente personalizzato né quali informazioni sincronizzare con Twitter. Verrà utilizzato il nome e il cognome per il nome utente e nel caso in cui risultasse già registrato verrà chiesto di scegliere un nome utente differente. Di default verranno sincronizzate tutte le informazioni possibili.";
$l['mytwconnect_settings_passwordpm'] = "Invia MP alla registrazione";
$l['mytwconnect_settings_passwordpm_desc'] = "Se quest'opzione è abilitata, quando un utente si registra con Twitter viene inviato un MP contenente la password generata casualmente durante la registrazione.";
$l['mytwconnect_settings_requestpublishingperms'] = "Richiedi permessi di pubblicazione";
$l['mytwconnect_settings_requestpublishingperms_desc'] = "Se quest'opzione è abilitata, quando un utente autorizza la tua applicazione verranno richiesti anche permessi per postare sulla propria timeline.";
$l['mytwconnect_settings_passwordpm'] = "Invia MP alla registrazione";
$l['mytwconnect_settings_passwordpm_desc'] = "Se quest'opzione è abilitata, verrà inviato un MP contenente la password generata casualmente ad ogni utente che si registra con Twitter.";
$l['mytwconnect_settings_passwordpm_subject'] = "Titolo dell'MP";
$l['mytwconnect_settings_passwordpm_subject_desc'] = "Scegli un titolo da dare all'MP da inviare.";
$l['mytwconnect_settings_passwordpm_message'] = "Messaggio dell'MP";
  $l['mytwconnect_settings_passwordpm_message_desc'] = "Scrivi un messaggio chiaro e conciso contenente qualche informazione e la password generata. Le variabili {user} e {password} si riferiscono rispettivamente al nome utente del nuovo utente registrato e alla password generata casualmente.";
$l['mytwconnect_settings_passwordpm_fromid'] = "Mittente";
$l['mytwconnect_settings_passwordpm_fromid_desc'] = "Inserisci l'UID del mittente dell'MP. Di default è 0 che equivale all'utente predefinito MyBB Engine, ma puoi modificarlo a piacimento. Assicurati che l'UID esista.";
// custom fields support, yay!
$l['mytwconnect_settings_twbday'] = "Sincronizza data di nascita";
$l['mytwconnect_settings_twbday_desc'] = "Se vuoi importare la data di nascita (e lasciare che gli utenti decidano se farlo) abilita quest'opzione.";
$l['mytwconnect_settings_twlocation'] = "Sincronizza località";
$l['mytwconnect_settings_twlocation_desc'] = "Se vuoi importare la località (e lasciare che gli utenti decidano se farlo) abilita quest'opzione.";
$l['mytwconnect_settings_twlocationfield'] = "ID del Campo Profilo personalizzato della località";
$l['mytwconnect_settings_twlocationfield_desc'] = "Inserisci l'ID del Campo Profilo personalizzato in cui verrà inserita la località in sincronizzazione. Di default è impostato a 1 (così come MyBB lo imposta allo startup).";
$l['mytwconnect_settings_twbio'] = "Sincronizza biografia";
$l['mytwconnect_settings_twbio_desc'] = "Se vuoi importare la biografia (e lasciare che gli utenti decidano se farlo) abilita quest'opzione.";
$l['mytwconnect_settings_twbiofield'] = "ID del Campo Profilo personalizzato della biografia";
$l['mytwconnect_settings_twbiofield_desc'] = "Inserisci l'ID del Campo Profilo personalizzato in cui verrà inserita la biografia in sincronizzazione. Di default è impostato a 2 (così come MyBB lo imposta allo startup).";
$l['mytwconnect_settings_twdetails'] = "Sincronizza nome e cognome";
$l['mytwconnect_settings_twdetails_desc'] = "Se vuoi importare nome e cognome (e lasciare che gli utenti decidano se farlo) abilita quest'opzione.";
$l['mytwconnect_settings_twdetailsfield'] = "ID del Campo Profilo personalizzato del nome e cognome";
$l['mytwconnect_settings_twdetailsfield_desc'] = "Inserisci l'ID del Campo Profilo personalizzato in cui verranno inseriti nome e cognome in sincronizzazione. Di default è vuoto (MyBB non lo prevede, puoi crearlo tu)";
$l['mytwconnect_settings_twsex'] = "Sincronizza sesso";
$l['mytwconnect_settings_twsex_desc'] = "<b>Quest'opzione funziona solo per board italiane.</b> Se sei italiano, puoi abilitarla e usarla normalmente. I valori inseriti saranno <b>Uomo</b> e <b>Donna</b>, a prescindere da quelli impostati nel Campo Profilo personalizzato corrispondente.";
$l['mytwconnect_settings_twsexfield'] = "ID del Campo Profilo personalizzato del sesso";
$l['mytwconnect_settings_twsexfield_desc'] = "Inserisci l'ID del Campo Profilo personalizzato in cui verrà inserito il sesso in sincronizzazione. Di default è impostato a 3 (così come MyBB lo imposta allo startup)";

// default pm text
$l['mytwconnect_default_passwordpm_subject'] = "Nuova password";
$l['mytwconnect_default_passwordpm_message'] = "Benvenuto sul nostro Forum, {user}!

Siamo felici ti sia registrato attraverso Twitter. Abbiamo generato una password casuale per il tuo account che solo tu conosci e che serve a modificare le informazioni personali come l'email, il nome utente e la password stessa che per motivi di sicurezza devono essere modificati conoscendo la password dell'account. Tienila segreta o cambiala a piacimento al più presto!

La tua password casuale è: [b]{password}[/b]

Distinti saluti,
il Team del Forum";

// errors
$l['mytwconnect_error_needtoupdate'] = "Sembra che tu abbia installato una versione non aggiornata di MyTwitter Connect. <a href=\"index.php?module=config-settings&upgrade=mytwconnect\">Clicca qui</a> per eseguire lo script di aggiornamento.";
$l['mytwconnect_error_nothingtodohere'] = "Ooops, MyTwitter Connect è già aggiornato all'ultima versione! Non c'è niente da fare qui...";

// success
$l['mytwconnect_success_updated'] = "MyTwitter Connect è stato aggiornato correttamente dalla versione {1} alla {2}.";