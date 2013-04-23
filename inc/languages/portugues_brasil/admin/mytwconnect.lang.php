<?php
// installation
$l['mytwconnect'] = "Conexão MyTwitter";
$l['mytwconnect_pluginlibrary_missing'] = "<a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> está faltando. Por favor, instale-o antes de fazer qualquer outra coisa com mytwconnect.";

// settings
$l['mytwconnect_settings'] = "Configurações de registro e acesso do Twitter";
$l['mytwconnect_settings_desc'] = "Aqui você pode gerenciar o login do Twitter e registro em seu conselho, alterando as chaves de API e opções para ativar ou desativar determinados aspectos do plugin do MyTwitter Connect.";
$l['mytwconnect_settings_enable'] = "Chave Mestra";
$l['mytwconnect_settings_enable_desc'] = "Você quer que seus usuários acessem e registrem com Twitter? Se um usuário já estiver registrado, a conta será vinculada à sua conta no Twitter.";
$l['mytwconnect_settings_conskey'] = "Consumer Key";
$l['mytwconnect_settings_conskey_desc'] = "Digite o seu token consumer key do Twitter do site Twitter Developers. Isso será usado em conjunto com o secret token para pedir autorização para seus usuários através de seu aplicativo.";
$l['mytwconnect_settings_conssecret'] = "Consumer Secret";
$l['mytwconnect_settings_conssecret_desc'] = "Digite seu secret token do Twitter do site Twitter Developers. Isso será usado em conjunto com o símbolo de chave de pedir autorização aos seus usuários através de seu aplicativo.";
$l['mytwconnect_settings_fastregistration'] = "Registro em 1-clique";
$l['mytwconnect_settings_fastregistration_desc'] = "Se esta opção for desativada, quando um usuário quiser registrar-se com o Twitter, será solicitado permissões para o aplicativo se é a primeira vez que ele é acessado, mas ele será cadastrado e logado imediatamente sem pedir alterações de nome de usuário e os dados que para sincronizar.";
$l['mytwconnect_settings_usergroup'] = "Grupo de usuários após o registro";
$l['mytwconnect_settings_usergroup_desc'] = "Identique o grupo de usuários após o registro com o Twitter. Por padrão= 2, o equivalente ao grupo de membros registrados.";
$l['mytwconnect_settings_requestpublishingperms'] = "Requisitando permissões de publicação";
$l['mytwconnect_settings_requestpublishingperms_desc'] = "Se esta opção for ativada, o usuário será solicitado para permissões de publicação extra para a sua aplicação. <b> Esta opção deve ser deixada desabilitada (como ele não vai fazer nada em particular no momento). No futuro, será fundamental para que você postar alguma coisa no mural do usuário quando ele registra-se ou acessa o seu fórum.";
$l['mytwconnect_settings_passwordpm'] = "Enviar MP após o regsitro";
$l['mytwconnect_settings_passwordpm_desc'] = "Se esta opção estiver ativada, o usuário será notificado com um MP dizendo a sua senha gerada aleatoriamente sobre a sua inscrição.";
$l['mytwconnect_settings_passwordpm_subject'] = "Título da Mensagem Pessoal";
$l['mytwconnect_settings_passwordpm_subject_desc'] = "Escolha um título padrão para a mensagem pessoal gerada.";
$l['mytwconnect_settings_passwordpm_message'] = "Mensagem Pessoal";
  $l['mytwconnect_settings_passwordpm_message_desc'] = "Write down a default message which will be sent to the registered users when they register with Twitter. {user} and {password} are variables and refer to the username the former and the randomly generated password the latter: they should be there even if you modify the default message. HTML and BBCode are permitted here.";
$l['mytwconnect_settings_passwordpm_fromid'] = "Endereçador da MP";
$l['mytwconnect_settings_passwordpm_fromid_desc'] = "Insira o UID do usuário que será o remetente da PM. Por padrão é definido como 0, que é MyBB, mas você pode alterá-lo para o que quiser.";
// custom fields support, yay!
$l['mytwconnect_settings_twlocation'] = "Sincronizar localidade";
$l['mytwconnect_settings_twlocation_desc'] = "Se você deseja importar localização do Twitter (e deixar os usuários decidirem)habilite esta opção.";
$l['mytwconnect_settings_twlocationfield'] = "Campo de Perfil referente à localidade";
$l['mytwconnect_settings_twlocationfield_desc'] = "Inserir o campo de perfil referente à localidade. Tenha certeza de inserir o correto! Padrão : 1 (MyBB's default)";
$l['mytwconnect_settings_twbio'] = "Sincronizar biografia";
$l['mytwconnect_settings_twbio_desc'] = "Se você deseja importar a biografia do Twitter (e deixar os usuários decidirem) habilite esta opção.";
$l['mytwconnect_settings_twbiofield'] = "Campo de Perfil referente à biografia";
$l['mytwconnect_settings_twbiofield_desc'] = "Insert the Custom Profile Field ID which corresponds to the Biography field. Make sure it's the right ID while you fill it! Default to 2 (MyBB's default)";

// default pm text
$l['mytwconnect_default_passwordpm_subject'] = "Senha Nova";
$l['mytwconnect_default_passwordpm_message'] = "Bem vindo ao nosso fórum, querido {user}!

Estamos felizes que você tenha utilizado o Twitter para registro. Nós geramos uma senha aleatória para você e você deve tomar nota em algum lugar, se você gostaria de mudar suas informações pessoais. Exigimos que por razões de segurança você especificaque sua senha quando você mudar as coisas, como e-mail, seu nome de usuário ea senha em si, de modo a manter em segredo!!

A sua senha é: [b]{password}[/b]

Além disso, devido ao fato de que não poderíamos buscar seu e-mail durante o processo de login do Twitter, temos registrado-o com um e-mail fictício. Nós recomendamos fortemente que você altere com um endereço de e-mail real para ser capaz de aceder a determinados serviços no futuro, como a senha de restauração do sistema.
Com carinho,
nossa equipe";

// errors
$l['mytwconnect_error_needtoupdate'] = "A sua versão do MyTwitter Connect está desatualizada!. Por favor <a href=\"index.php?module=config-settings&upgrade=mytwconnect\">clique aqui</a> para atualizar o script de atualização.";
$l['mytwconnect_error_nothingtodohere'] = "Opa!! MyTwitter Connect está atualizado, nada a fazer!!!";

// success
$l['mytwconnect_success_updated'] = "MyTwitter Connect atualizou corretamente as versões {1} para {2}. Bom trabalho!";
