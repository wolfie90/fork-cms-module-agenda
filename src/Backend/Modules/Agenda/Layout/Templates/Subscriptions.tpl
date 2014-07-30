{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblAgenda|ucfirst}: {$lblSubscriptions|ucfirst}</h2>
</div>

<div id="tabs" class="tabs">
	<ul>
		<li><a href="#tabModeration">{$lblWaitingForModeration|ucfirst} ({$numModeration})</a></li>
		<li><a href="#tabSubscribed">{$lblSubscribed|ucfirst} ({$numSubscriptions})</a></li>
	</ul>

	<div id="tabModeration">
		{option:dgModeration}
			<form action="{$var|geturl:'mass_subscriptions_action'}" method="get" class="forkForms" id="subscriptionsModeration">
				<div class="dataGridHolder">
					<input type="hidden" name="from" value="moderation" />
					{$dgModeration}
				</div>
			</form>
		{/option:dgModeration}
		{option:!dgModeration}{$msgNoAgendaSubscriptions}.{/option:!dgModeration}
	</div>

	<div id="tabSubscribed">
		{option:dgSubscribed}
			<form action="{$var|geturl:'mass_subscriptions_action'}" method="get" class="forkForms" id="subscriptionsSubscribed">
				<div class="dataGridHolder">
					<input type="hidden" name="from" value="subscribe" />
					<div class="generalMessage infoMessage">
						{$msgDeleteAllSubscriptions}
						<a href="{$var|geturl:'delete_subscribed'}">{$lblDelete|ucfirst}</a>
					</div>
					{$dgSubscribed}
				</div>
			</form>
		{/option:dgSubscribed}
		{option:!dgSubscribed}{$msgNoAgendaSubscriptions}.{/option:!dgSubscribed}
	</div>
</div>

<div id="confirmDeleteModeration" title="{$lblDelete|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassDelete}</p>
</div>
<div id="confirmSubscribeModeration" title="{$lblSubscribe|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassSubscribed}</p>
</div>
<div id="confirmDeleteSubscribed" title="{$lblDelete|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassDelete}</p>
</div>

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}