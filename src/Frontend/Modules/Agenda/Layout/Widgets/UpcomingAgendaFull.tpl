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
					<h4><a href="{$widgetUpcomingAgendaFull.full_url}" title="{$widgetUpcomingAgendaFull.title}">{$widgetUpcomingAgendaFull.title}</a></h4>
                    <div class="date">
                        <time itemprop="date" datetime="{$widgetUpcomingAgendaFull.begin_date|date:'Y-m-d\TH:i:s'}">{$widgetUpcomingAgendaFull.begin_date|date:{$dateFormatLong}:{$LANGUAGE}}</time>
                        <time itemprop="time" datetime="{$widgetUpcomingAgendaFull.begin_date|date:'Y-m-d\TH:i:s'}">{$widgetUpcomingAgendaFull.begin_date|date:{$timeFormat}:{$LANGUAGE}}</time>
                    </div>
                    <p>{$widgetUpcomingAgendaFull.introduction}</p>
					<p>
						<a href="{$widgetUpcomingAgendaFull.full_url}" title="{$widgetUpcomingAgendaFull.title}">{$lblReadMore|ucfirst}</a>
					</p>
				</li>
			{/iteration:widgetUpcomingAgendaFull}
		</ul>
		<footer>
			<p>
				<a href="{$var|geturlforblock:'agenda'}">{$lblAllAgendaItems|ucfirst}</a>
			</p>
		</footer>
	</section>
{/option:widgetUpcomingAgendaFull}
