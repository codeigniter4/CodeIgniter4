################
User Agent Class
################

The User Agent Class provides functions that help identify information
about the browser, mobile device, or robot visiting your site.

.. contents::
    :local:
    :depth: 2

**************************
Using the User Agent Class
**************************

Initializing the Class
======================

The User Agent class is always available directly from the current :doc:`IncomingRequest </incoming/incomingrequest>` instance.
By default, you will have a request instance in your controller that you can retrieve the
User Agent class from:

.. literalinclude:: user_agent/001.php

User Agent Definitions
======================

The user agent name definitions are located in a config file located at:
**app/Config/UserAgents.php**. You may add items to the various
user agent arrays if needed.

Example
=======

When the User Agent class is initialized it will attempt to determine
whether the user agent browsing your site is a web browser, a mobile
device, or a robot. It will also gather the platform information if it
is available:

.. literalinclude:: user_agent/002.php

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\HTTP

.. php:class:: UserAgent

    .. php:method:: isBrowser([$key = null])

        :param    string    $key: Optional browser name
        :returns:    true if the user agent is a (specified) browser, false if not
        :rtype:    bool

        Returns true/false (boolean) if the user agent is a known web browser.

        .. literalinclude:: user_agent/003.php

        .. note:: The string "Safari" in this example is an array key in the list of browser definitions.
                  You can find this list in **app/Config/UserAgents.php** if you want to add new
                  browsers or change the strings.

    .. php:method:: isMobile([$key = null])

        :param    string    $key: Optional mobile device name
        :returns:    true if the user agent is a (specified) mobile device, false if not
        :rtype:    bool

        Returns true/false (boolean) if the user agent is a known mobile device.

        .. literalinclude:: user_agent/004.php

    .. php:method:: isRobot([$key = null])

        :param    string    $key: Optional robot name
        :returns:    true if the user agent is a (specified) robot, false if not
        :rtype:    bool

        Returns true/false (boolean) if the user agent is a known robot.

        .. note:: The user agent library only contains the most common robot definitions. It is not a complete list of bots.
                  There are hundreds of them so searching for each one would not be very efficient. If you find that some bots
                  that commonly visit your site are missing from the list you can add them to your
                  **app/Config/UserAgents.php** file.

    .. php:method:: isReferral()

        :returns:    true if the user agent is a referral, false if not
        :rtype:    bool

        Returns true/false (boolean) if the user agent was referred from another site.

    .. php:method:: getBrowser()

        :returns:    Detected browser or an empty string
        :rtype:    string

        Returns a string containing the name of the web browser viewing your site.

    .. php:method:: getVersion()

        :returns:    Detected browser version or an empty string
        :rtype:    string

        Returns a string containing the version number of the web browser viewing your site.

    .. php:method:: getMobile()

        :returns:    Detected mobile device brand or an empty string
        :rtype:    string

        Returns a string containing the name of the mobile device viewing your site.

    .. php:method:: getRobot()

        :returns:    Detected robot name or an empty string
        :rtype:    string

        Returns a string containing the name of the robot viewing your site.

    .. php:method:: getPlatform()

        :returns:    Detected operating system or an empty string
        :rtype:    string

        Returns a string containing the platform viewing your site (Linux, Windows, OS X, etc.).

    .. php:method:: getReferrer()

        :returns:    Detected referrer or an empty string
        :rtype:    string

        The referrer, if the user agent was referred from another site. Typically you'll test for this as follows:

        .. literalinclude:: user_agent/005.php

    .. php:method:: getAgentString()

        :returns:    Full user agent string or an empty string
        :rtype:    string

        Returns a string containing the full user agent string. Typically it will be something like this::

            Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.0.4) Gecko/20060613 Camino/1.0.2

    .. php:method:: parse($string)

        :param    string    $string: A custom user-agent string
        :rtype:    void

        Parses a custom user-agent string, different from the one reported by the current visitor.
