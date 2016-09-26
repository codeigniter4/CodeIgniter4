IncomingRequest Class
######################

Class IncomingRequest

Represents an incoming, getServer-side HTTP request.

Per the HTTP specification, this interface includes properties for
each of the following:

- Protocol version
- HTTP method
- URI
- Headers
- Message body

Additionally, it encapsulates all data as it has arrived to the
application from the CGI and/or PHP environment, including:

- The values represented in $_SERVER.
- Any cookies provided (generally via $_COOKIE)
- Query string arguments (generally via $_GET, or as parsed via parse_str())
- Upload files, if any (as represented by $_FILES)
- Deserialized body binds (generally from $_POST)


.. php:class:: CodeIgniter\\HTTP\\IncomingRequest

	Implements: CodeIgniter\HTTP\RequestInterface

	.. php:method:: __construct ( $config [ $uri [, $body ]] )

		:param $config: 
		:param $uri: 
		:param $body: 


		


	.. php:method:: isCLI (  )

		:returns: 
		:rtype: bool

		Determines if this request was made from the command line (CLI).



	.. php:method:: isAJAX (  )

		:returns: 
		:rtype: bool

		Test to see if a request contains the HTTP_X_REQUESTED_WITH header.



	.. php:method:: getGet ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from GET data.




	.. php:method:: getPost ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from POST.




	.. php:method:: getPostGet ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from POST data with fallback to GET.




	.. php:method:: getGetPost ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from GET data with fallback to POST.




	.. php:method:: getCookie ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from the COOKIE array.




	.. php:method:: getUserAgent ( [ $filter ] )

		:param $filter: 


		Fetch the user agent string



	.. php:method:: setCookie ( $name [ $value [, $expire [, $domain [, $path [, $prefix [, $secure [, $httponly ]]]]]]] )

		:param $name: 
		:param $value: 
		:param $expire: 
		:param $domain: 
		:param $path: 
		:param $prefix: 
		:param $secure: 
		:param $httponly: 


		Set a cookie

		Accepts an arbitrary number of binds (up to 7) or an associateive
		array in the first parameter containing all the values.



	.. php:method:: isSecure (  )

		:returns: 
		:rtype: bool

		Attempts to detect if the current connection is secure through
		a few different methods.



	.. php:method:: getFiles (  )

		:returns: 
		:rtype: array

		Returns an array of all files that have been uploaded with this
		request. Each file is represented by an UploadedFile instance.



	.. php:method:: getFile ( string $fileID  )

		:param string $fileID: 


		Retrieves a single file by the name of the input field used
		to upload it.




	.. php:method:: detectPath ( $protocol  )

		:param $protocol: 


		Based on the URIProtocol Config setting, will attempt to
		detect the path portion of the current URI.




	.. php:method:: negotiate ( string $type array $supported [ bool $strictMatch ] )

		:param string $type: 
		:param array $supported: 
		:param bool $strictMatch: 


		Provides a convenient way to work with the Negotiate class
		for content negotiation.




	.. php:method:: getIPAddress (  )

		:returns: 
		:rtype: string

		Gets the user's IP address.



	.. php:method:: isValidIP ( string $ip [ string $which ] )

		:param string $ip: 
		:param string $which: 
		:returns: 
		:rtype: bool

		Validate an IP address




	.. php:method:: getMethod ( [ $upper ] )

		:param $upper: 
		:returns: 
		:rtype: string

		Get the request method.




	.. php:method:: getServer ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from the $_SERVER array.



	.. php:method:: getEnv ( [ $index [, $filter ]] )

		:param $index: 
		:param $filter: 


		Fetch an item from the $_ENV array.



	.. php:method:: getBody (  )



		Returns the Message's body.



	.. php:method:: setBody ( &$data  )

		:param $data: 
		:returns: 
		:rtype: self

		Sets the body of the current message.




	.. php:method:: populateHeaders (  )



		Populates the $headers array with any headers the getServer knows about.


	.. php:method:: getHeaders (  )

		:returns: 
		:rtype: array

		Returns an array containing all headers.



	.. php:method:: getHeader ( $name  )

		:param $name: 


		Returns a single header object. If multiple headers with the same
		name exist, then will return an array of header objects.




	.. php:method:: hasHeader ( $name  )

		:param $name: 
		:returns: 
		:rtype: bool

		Determines whether a header exists.




	.. php:method:: getHeaderLine ( string $name  )

		:param string $name: 
		:returns: 
		:rtype: string

		Retrieves a comma-separated string of the values for a single header.

		This method returns all of the header values of the given
		case-insensitive header name as a string concatenated together using
		a comma.

		NOTE: Not all header values may be appropriately represented using
		comma concatenation. For such headers, use getHeader() instead
		and supply your own delimiter when concatenating.




	.. php:method:: setHeader ( string $name $value  )

		:param string $name: 
		:param $value: 
		:returns: 
		:rtype: self

		Sets a header and it's value.




	.. php:method:: removeHeader ( string $name  )

		:param string $name: 
		:returns: 
		:rtype: self

		Removes a header from the list of headers we track.




	.. php:method:: appendHeader ( string $name $value  )

		:param string $name: 
		:param $value: 
		:returns: 
		:rtype: self

		Adds an additional header value to any headers that accept
		multiple values (i.e. are an array or implement ArrayAccess)




	.. php:method:: prependHeader ( string $name $value  )

		:param string $name: 
		:param $value: 
		:returns: 
		:rtype: self

		Adds an additional header value to any headers that accept
		multiple values (i.e. are an array or implement ArrayAccess)




	.. php:method:: getProtocolVersion (  )

		:returns: 
		:rtype: string

		Returns the HTTP Protocol Version.



	.. php:method:: setProtocolVersion ( string $version  )

		:param string $version: 
		:returns: 
		:rtype: self

		Sets the HTTP protocol version.





