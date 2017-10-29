<h3>Matched Route</h3>

<table>
    <tbody>
    {matchedRoute}
        <tr>
            <td>Directory:</td>
            <td>{directory}</td>
        </tr>
        <tr>
            <td>Controller:</td>
            <td>{controller}</td>
        </tr>
        <tr>
            <td>Method:</td>
            <td>{method}</td>
        </tr>
        <tr>
            <td>Params:</td>
            <td>{paramCount} / {truePCount}</td>
        </tr>
        {params}
            <tr class="route-params-item">
                <td>{name}</td>
                <td>{value}</td>
            </tr>
        {/params}
    {/matchedRoute}
    </tbody>
</table>


<h3>Defined Routes</h3>

<table>
    <tbody>
    {routes}
        <tr>
            <td>{from}</td>
            <td>{to}</td>
        </tr>
    {/routes}
    </tbody>
</table>
