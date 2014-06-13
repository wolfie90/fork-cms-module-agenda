{*
	variables that are available:
	- {$widgetUpcomingAgendaFull}:
*}

{option:widgetUpcomingAgendaFull}
	<section id="UpcomingAgendaItems">
		<header>
			<h3>{$lblUpcomingAgendaItems|ucfirst}</h3>
		</header>
		<ul>
			{iteration:widgetUpcomingAgendaFull}
				<li>
					<div class="date">
						<time itemprop="datePublished" datetime="{$widgetUpcomingAgendaFull.begin_date|date:'d-M'}"><span>{$widgetUpcomingAgendaFull.begin_date|date:'d':{$LANGUAGE}}</span>{$widgetUpcomingAgendaFull.begin_date|date:'M':{$LANGUAGE}}</time>
					</div>
					<h4><a href="{$widgetUpcomingAgendaFull.full_url}" title="{$widgetUpcomingAgendaFull.title}">{$widgetUpcomingAgendaFull.title}</a></h4>
					{$widgetUpcomingAgendaFull.introduction}
					<p class="more">
						<a href="{$widgetUpcomingAgendaFull.full_url}" title="{$widgetUpcomingAgendaFull.title}">{$lblReadMore|ucfirst}</a>
					</p>
				</li>
			{/iteration:widgetUpcomingAgendaFull}
		</ul>
		<footer>
			<p>
				<a href="{$var|geturlforblock:'agenda'}" class="buttongrey">{$lblAllAgendaItems|ucfirst}</a>
			</p>
		</footer>
	</section>
{/option:widgetUpcomingAgendaFull}
