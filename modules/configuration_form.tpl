
<link href='../modules/{$name}/css/configuration.css' type='text/css' rel='stylesheet' media='all' />    

<form id='configuration_form' class='defaultForm {$name}-configuration form-horizontal' method='post' enctype='multipart/form-data'>	             

<div class="panel">
	<h3>
		<i class='icon-list-alt'></i>
		Módulos Neogest
	</h3>
	<ul class="ngcliente-neogest-modules">
		{foreach from=$modules_neogest item=module}
      <li class="ngcliente-neogest-module {if $module->module->is_on_store} onStore {/if}" data-modulename="{$module->module->module_name}">
        <h4>
          {$module->product->name}
        </h4>

        <span>
          Versões do PrestaShop: de <span class="ngsmall">{$module->version->minimal_ps_version}</span> a <span class="ngsmall">{$module->version->maximum_ps_version}</span>.
        </span>

        <p>
          {$module->product->description}
        </p>

        <span class="ngcliente-neogest-module-download ngbutton">Download</span>
        <a href="{$module->product->link}" target="blank" class="ngbutton">
          Visualizar
        </a>
      </li>
		{/foreach}
	</ul>
</div>
