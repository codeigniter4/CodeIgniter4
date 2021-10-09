<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th>Query String</th>
        </tr>
    </thead>
    <tbody>
    {queries}
        <tr class="{class}" title="{hover}">
            <td class="narrow">{duration}</td>
            <td>{! sql !}</td>
        </tr>
    {/queries}
    </tbody>
</table>
