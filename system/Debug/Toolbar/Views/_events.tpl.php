<table>
    <thead>
        <tr>
            <th style="width: 6rem;">Time</th>
            <th>Event Name</th>
            <th>Times Called</th>
        </tr>
    </thead>
    <tbody>
    {events}
        <tr>
            <td class="narrow">{ duration } ms</td>
            <td>{event}</td>
            <td>{count}</td>
        </tr>
    {/events}
    </tbody>
</table>
