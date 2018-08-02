<table>
    <thead>
        <tr>
            <th>Action</th>
            <th>Datetime</th>
            <th>Status</th>
            <th>Method</th>
            <th>URL</th>
            <th>Content-Type</th>
            <th>Is AJAX?</th>
        </tr>
    </thead>
    <tbody>
    {files}
        <tr data-active="{active}">
        	<td class="debug-bar-width70p">
            	<button class="ci-history-load" data-time="{time}">Load</button>
            </td>
            <td class="debug-bar-width140p">{datetime}</td>
            <td>{status}</td>
            <td>{method}</td>
            <td>{url}</td>
            <td>{contentType}</td>
            <td>{isAJAX}</td>
        </tr>
    {/files}
    </tbody>
</table>
