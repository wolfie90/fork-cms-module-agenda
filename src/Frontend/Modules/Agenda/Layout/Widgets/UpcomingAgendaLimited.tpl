{*
	variables that are available:
	- {$widgetUpcomingAgendaLimited}:
*}

{option:widgetUpcomingAgendaLimited}
    <section id="UpcomingAgendaItems">
        <header>
            <h3>{$lblUpcomingAgendaItems|ucfirst}</h3>
        </header>
        <ul>
            {iteration:widgetUpcomingAgendaLimited}
                <li>
                    <h4><a href="{$widgetUpcomingAgendaLimited.full_url}" title="{$widgetUpcomingAgendaLimited.title}">{$widgetUpcomingAgendaLimited.title}</a></h4>
                    <div class="date">
                        <time itemprop="date" datetime="{$widgetUpcomingAgendaLimited.begin_date|date:'Y-m-d\TH:i:s'}">{$widgetUpcomingAgendaLimited.begin_date|date:{$dateFormatLong}:{$LANGUAGE}}</time>
                        <time itemprop="time" datetime="{$widgetUpcomingAgendaLimited.begin_date|date:'Y-m-d\TH:i:s'}">{$widgetUpcomingAgendaLimited.begin_date|date:{$timeFormat}:{$LANGUAGE}}</time>
                    </div>
                    <p>{$widgetUpcomingAgendaLimited.introduction}</p>
                    <p>
                        <a href="{$widgetUpcomingAgendaLimited.full_url}" title="{$widgetUpcomingAgendaLimited.title}">{$lblReadMore|ucfirst}</a>
                    </p>
                </li>
            {/iteration:widgetUpcomingAgendaLimited}
        </ul>
        <footer>
            <p>
                <a href="{$var|geturlforblock:'agenda'}">{$lblAllAgendaItems|ucfirst}</a>
            </p>
        </footer>
    </section>
{/option:widgetUpcomingAgendaLimited}
