  <h1 itemprop="name">{$lblCategory|ucfirst}: {$title}</h1>

  <div id="data-agenda">
    <h3>{$lblAllAgendaThisWeek}:</h3>
    {option:items}
      <div class="agendaItems">
          <table class="responsive">
             <thead>
                <tr>
                    <th>{$lblTitle|ucfirst}</th>
                    <th>{$lblCategory|ucfirst}</th>
                    <th>{$lblDate|ucfirst}</th>
                    <th>{$lblTime|ucfirst}</th>
                    <!--<th>Plaats</th>-->
                </tr>
              </thead>
             <tbody>
              {iteration:items}
                  <tr>
                      <td data-title="title"><a href="{$items.full_url}"><b>{$items.title}</b></a></td>
                      <td data-title="category"><a href="{$items.category_full_url}"><span class="category" style="color: {$items.category_color}">{$items.category_title}</span></a></td>
                      <td data-title="date">
                         <time itemprop="date" datetime="{$items.begin_date|date:'Y-m-d\TH:i:s'}">{$items.begin_date|date:{$dateFormatLong}:{$LANGUAGE}}</time>
                      </td>
                      <td data-title="time">
                         <time itemprop="time" datetime="{$items.begin_date|date:'Y-m-d\TH:i:s'}">{$items.begin_date|date:{$timeFormat}:{$LANGUAGE}}</time>
                         {option:enddate}
                         - <time itemprop="time" datetime="{$items.end_date|date:'Y-m-d\TH:i:s'}">{$items.end_date|date:{$timeFormat}:{$LANGUAGE}}</time>
                         {/option:enddate}
                      </td>
                      <!--<td data-title="venue">{$items.venue}</td>-->
                  </tr>
              {/iteration:items}
              </tbody>
          </table>
      </div>
   {/option:items}
   {option:!items}
       <p class="date">{$msgNoAgenda|ucfirst}.</p>
   {/option:!items}
    </div>
  </div>


