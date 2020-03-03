<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to CodeIgniter <?= CodeIgniter\CodeIgniter::CI_VERSION ?></title>
  <meta name="description" content="The small framework with powerful features">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" type="image/png" href="/favicon.ico"/>

  <!-- STYLES -->

  <style {csp-style-nonce}>
  * {
    transition: background-color 300ms ease, color 300ms ease; }

  *:focus {
    background-color: #F9F3F3;
    outline: none; }

  html, body {
    color: #252525;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
    font-size: 16px;
    font-weight: 400; }

  header {
    border-bottom: 1px solid #f4f4f4;
    background-color: #FAFAFA;
    padding: .4rem 0 0; }

  header .menu {
    border-bottom: 1px solid #f4f4f4;
    padding: .4rem 2rem; }

  header ul {
    overflow: hidden;
    margin: 0;
    padding: 0;
    list-style-type: none;
    text-align: right; }

  header li {
    display: inline-block; }

  header li a {
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    color: #434343;
    height: 44px;
    display: block;
    font-weight: 300;
    text-decoration: none; }

  header li.menu-item a {
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    height: 38px;
    line-height: 36px;
    margin: 5px 0;
    padding: .4rem .65rem;
    text-align: center; }

  header li.menu-item a:hover,
  header li.menu-item a:focus {
    background-color: #F9F3F3;
    color: #DD4814; }

  header .logo {
    height: 44px;
    float: left;
    padding: .4rem .5rem; }

  header .menu-toggle {
    display: none;
    float: right;
    font-size: 2rem;
    font-weight: bold; }

  header .menu-toggle button,
  header .menu-toggle button:hover,
  header .menu-toggle button:focus {
    border: none;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    background-color: #DD4814;
    color: #FFFFFF;
    height: 36px;
    width: 40px;
    cursor: pointer;
    overflow: visible;
    margin: 11px 0;
    padding: 0;
    font-size: 1.3rem; }

  header .heroe {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1rem 1.75rem 1.75rem 1.75rem; }

  header .heroe h1 {
    font-size: 2.5rem;
    font-weight: 500; }

  header .heroe h2 {
    font-size: 1.5rem;
    font-weight: 300; }

  @media (max-width: 559px) {
    header ul {
      margin-bottom: 1rem;
      padding: 0; }

    header .menu-toggle {
      padding: 0 1rem; }

    header .menu-item {
      background-color: none;
      width: calc(100% - 0.15rem);
      margin: 0.15rem 0; }

    header .menu-toggle {
      display: block; }

    header .hidden {
      display: none; }

    header li.menu-item a {
      background-color: #f4f4f4; }

    header li.menu-item a:hover,
    header li.menu-item a:focus {
      background-color: #F9F3F3;
      color: #DD4814; } }
  section .content {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2.5rem 1.75rem 3.5rem 1.75rem; }

  section h1 {
    margin-bottom: 2.5rem; }

  section h2 {
    margin: 2.5rem 0 3rem 0;
    font-size: 150%; }

  section h3 {
    margin: 3rem 0 1.5rem 0;
    font-size: 125%; }

  section pre {
    border: 1px solid #f4f4f4;
    background-color: #FAFAFA;
    display: block;
    margin: 2rem 0;
    padding: 1rem 1.5rem;
    white-space: pre-wrap;
    word-break: break-all;
    font-size: .9rem; }

  section code {
    display: block; }

  section a {
    color: #DD4814; }

  section svg {
    width: 25px;
    margin-bottom: -5px;
    margin-right: 5px; }

  section:nth-of-type(even) {
    border-bottom: 1px solid #f4f4f4;
    border-top: 1px solid #f4f4f4;
    background-color: #FAFAFA; }

  section .mini-buttons {
    margin: 2rem 0 3rem 0;
    font-size: 85%; }

  section .button {
    border: 1px solid #EF9090;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    color: #DD4814;
    margin-right: 0.5rem;
    padding: 0.3rem 0.6rem;
    text-decoration: none; }

  section .button:hover,
  section .button:active {
    background-color: #F9F3F3; }

  footer {
    border-top: 1px solid #f4f4f4;
    background-color: #DD4814;
    color: #FFFFFF;
    text-align: center; }

  footer .environment {
    padding: 2rem 1.75rem; }

  footer .copyrights {
    background-color: #434343;
    color: #DFDFDF;
    padding: 0.25rem 1.75rem;
    font-size: 80%; }
  </style>
</head>
<body>

<!-- HEADER: MENU + HEROE SECTION -->
<header>

  <div class="menu">
    <ul>
      <li class="logo">
        <a href="https://codeigniter.com" target="_blank" title="CodeIgniter.com">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2100 500" width="185" height="44">
            <style>
              tspan { white-space:pre }
              .shp0 { fill: #dd4814 }
            </style>
            <path class="shp0" d="M148.2 411C127.67 401.93 113.72 382.39 111.89 360.01C113.09 336.99 125.25 315.95 144.56 303.4C141.39 311.13 142.16 319.93 146.56 327C151.57 334 160.19 337.36 168.63 335.61C180.65 332.23 187.69 319.75 184.31 307.72C183.11 303.51 180.71 299.69 177.43 296.81C163.83 285.75 157 268.37 159.43 251C161.76 241.8 166.85 233.48 174.04 227.2C168.64 241.6 183.87 255.81 194.09 262.8C212.23 273.68 229.69 285.64 246.41 298.61C264.68 313.01 274.64 335.55 273.08 358.61C268.97 383.15 251.61 403.41 227.95 411.01C275.28 400.48 324.08 362.88 325.01 309.55C324.08 266.88 298.61 228.59 259.68 211.15L257.95 211.15C258.81 213.24 259.23 215.49 259.15 217.76C259.28 216.29 259.28 214.83 259.15 213.36C259.36 215.09 259.36 216.83 259.15 218.56C256.19 230.69 243.95 238.16 231.79 235.2C226.93 234 222.59 231.27 219.47 227.33C203.87 207.33 219.47 184.57 222.08 162.57C223.68 134.44 210.83 107.55 188.03 91.11C199.44 110.13 184.24 135.11 173.19 149.32C162.12 163.53 146.12 174.12 133.08 186.52C119.03 199.59 106.15 213.96 94.59 229.32C69.6 259.85 59.79 300.12 67.92 338.72C79.07 375.92 109.99 403.87 148.12 411.12L148.33 411.12L148.2 411Z" />
            <path fill="#dd4814" d="M472.76 251.2Q472.76 233.28 478.92 215.64Q485.36 197.72 497.4 183.72Q509.44 169.72 527.08 161.04Q544.72 152.08 567.12 152.08Q593.72 152.08 612.48 164.12Q631.52 176.16 640.48 195.48L625.08 205Q620.32 195.2 613.32 188.48Q606.6 181.76 598.76 177.56Q590.92 173.36 582.52 171.68Q574.12 169.72 566 169.72Q548.08 169.72 534.36 177Q520.64 184.28 511.12 196.04Q501.88 207.8 497.12 222.64Q492.36 237.2 492.36 252.32Q492.36 268.84 497.96 283.96Q503.84 299.08 513.64 310.84Q523.72 322.32 537.16 329.32Q550.88 336.04 566.84 336.04Q575.24 336.04 583.92 334.08Q592.88 331.84 601 327.36Q609.4 322.6 616.4 315.88Q623.4 308.88 628.16 299.08L644.4 307.48Q639.64 318.68 630.96 327.36Q622.28 335.76 611.64 341.64Q601 347.52 588.96 350.6Q577.2 353.68 565.72 353.68Q545.28 353.68 528.2 344.72Q511.12 335.76 498.8 321.48Q486.48 306.92 479.48 288.72Q472.76 270.24 472.76 251.2ZM736.24 354.8Q720.28 354.8 707.12 348.92Q693.96 342.76 684.16 332.4Q674.64 322.04 669.32 308.32Q664 294.6 664 279.48Q664 264.08 669.32 250.36Q674.92 236.64 684.44 226.28Q694.24 215.92 707.4 209.76Q720.56 203.6 736.24 203.6Q751.92 203.6 765.08 209.76Q778.52 215.92 788.04 226.28Q797.84 236.64 803.16 250.36Q808.76 264.08 808.76 279.48Q808.76 294.6 803.44 308.32Q798.12 322.04 788.32 332.4Q778.8 342.76 765.36 348.92Q752.2 354.8 736.24 354.8ZM683.32 279.76Q683.32 292.08 687.52 302.72Q691.72 313.08 698.72 321.2Q706 329.04 715.52 333.52Q725.32 338 736.24 338Q747.16 338 756.68 333.52Q766.48 328.76 773.76 320.92Q781.04 312.8 785.24 302.16Q789.44 291.52 789.44 279.2Q789.44 267.16 785.24 256.52Q781.04 245.6 773.76 237.76Q766.48 229.64 756.68 225.16Q747.16 220.4 736.24 220.4Q725.32 220.4 715.8 225.16Q706.28 229.64 699 237.76Q691.72 245.88 687.52 256.8Q683.32 267.44 683.32 279.76ZM900.6 354.8Q885.48 354.8 872.6 348.64Q859.72 342.2 850.48 331.84Q841.24 321.48 835.92 308.04Q830.88 294.32 830.88 279.48Q830.88 264.08 835.92 250.36Q840.96 236.36 849.64 226Q858.6 215.64 870.64 209.76Q882.96 203.6 897.52 203.6Q916 203.6 930.28 213.4Q944.56 222.92 952.68 236.64L952.68 147.6L971.72 147.6L971.72 327.36Q971.72 335.2 978.44 335.2L978.44 352Q974.24 352.84 971.72 352.84Q965 352.84 959.96 348.64Q954.92 344.16 954.92 338L954.92 323.72Q946.24 338 931.4 346.4Q916.56 354.8 900.6 354.8ZM904.8 338Q911.8 338 919.64 335.2Q927.76 332.4 934.76 327.64Q941.76 322.6 946.52 316.16Q951.56 309.44 952.68 301.88L952.68 256.8Q949.88 249.24 944.56 242.8Q939.24 236.08 932.24 231.04Q925.52 226 917.68 223.2Q909.84 220.4 902.28 220.4Q890.52 220.4 881 225.44Q871.48 230.48 864.48 238.88Q857.76 247 854.12 257.64Q850.48 268.28 850.48 279.48Q850.48 291.24 854.68 301.88Q858.88 312.52 866.16 320.64Q873.44 328.48 883.24 333.24Q893.32 338 904.8 338ZM1077.84 354.8Q1061.88 354.8 1048.44 348.92Q1035.28 342.76 1025.48 332.4Q1015.68 321.76 1010.08 308.04Q1004.76 294.32 1004.76 278.64Q1004.76 263.24 1010.08 249.8Q1015.68 236.08 1025.2 226Q1035 215.64 1048.44 209.76Q1061.88 203.6 1077.56 203.6Q1093.52 203.6 1106.68 209.76Q1120.12 215.64 1129.64 226Q1139.16 236.36 1144.48 249.8Q1149.8 263.24 1149.8 278.36Q1149.8 280.6 1149.8 282.84Q1149.8 285.08 1149.52 285.92L1024.64 285.92Q1025.48 297.68 1029.96 307.76Q1034.72 317.56 1042 324.84Q1049.28 332.12 1058.52 336.32Q1068.04 340.24 1078.68 340.24Q1085.68 340.24 1092.68 338.28Q1099.68 336.32 1105.56 332.96Q1111.44 329.6 1116.2 324.84Q1120.96 319.8 1123.48 313.92L1140 318.4Q1136.64 326.52 1130.48 333.24Q1124.32 339.68 1116.2 344.72Q1108.08 349.48 1098.28 352.28Q1088.48 354.8 1077.84 354.8ZM1024.36 271.36L1131.6 271.36Q1130.76 259.6 1126 250.08Q1121.52 240.28 1114.24 233.28Q1107.24 226.28 1097.72 222.36Q1088.48 218.44 1077.84 218.44Q1067.2 218.44 1057.68 222.36Q1048.16 226.28 1040.88 233.28Q1033.88 240.28 1029.4 250.08Q1025.2 259.88 1024.36 271.36ZM1217.56 352L1178.92 352L1178.92 153.2L1217.56 153.2L1217.56 352ZM1311.08 352.84Q1296.24 352.84 1284.2 346.96Q1272.16 341.08 1263.2 331Q1254.52 320.64 1249.76 307.2Q1245 293.76 1245 278.64Q1245 262.68 1250.04 248.96Q1255.08 235.24 1264.04 224.88Q1273 214.52 1285.6 208.64Q1298.2 202.76 1313.32 202.76Q1330.4 202.76 1343.28 210.6Q1356.16 218.16 1364.56 231.04L1364.56 205.28L1397.32 205.28L1397.32 345Q1397.32 361.24 1391.16 374.12Q1385 387 1374.08 395.96Q1363.44 404.92 1348.32 409.68Q1333.48 414.44 1315.84 414.44Q1291.76 414.44 1275.24 406.6Q1259 398.48 1247.24 383.92L1267.68 364.04Q1276.08 374.4 1288.68 380.28Q1301.56 386.16 1315.84 386.16Q1324.52 386.16 1332.36 383.92Q1340.48 381.4 1346.64 376.36Q1352.8 371.32 1356.16 363.48Q1359.8 355.64 1359.8 345L1359.8 326.52Q1352.52 339.12 1339.36 346.12Q1326.2 352.84 1311.08 352.84ZM1323.68 322.88Q1329.84 322.88 1335.44 320.92Q1341.04 318.96 1345.8 315.6Q1350.56 312.24 1354.2 307.76Q1357.84 303.28 1359.8 298.24L1359.8 263.24Q1354.76 250.36 1343.84 242.52Q1333.2 234.68 1321.44 234.68Q1312.76 234.68 1305.76 238.6Q1298.76 242.24 1293.72 248.68Q1288.68 254.84 1285.88 262.96Q1283.36 271.08 1283.36 279.76Q1283.36 288.72 1286.44 296.56Q1289.52 304.4 1294.84 310.28Q1300.44 316.16 1307.72 319.52Q1315 322.88 1323.68 322.88ZM1567.56 260.44L1567.56 352L1530.04 352L1530.04 269.68Q1530.04 252.04 1523.88 243.92Q1517.72 235.8 1506.8 235.8Q1501.2 235.8 1495.32 238.04Q1489.44 240.28 1484.12 244.48Q1479.08 248.4 1474.88 254Q1470.68 259.6 1468.72 266.32L1468.72 352L1431.2 352L1431.2 205.28L1465.08 205.28L1465.08 232.44Q1473.2 218.44 1488.6 210.6Q1504 202.76 1523.32 202.76Q1537.04 202.76 1545.72 207.8Q1554.4 212.84 1559.16 220.96Q1563.92 229.08 1565.6 239.44Q1567.56 249.8 1567.56 260.44ZM1637.84 352L1600.32 352L1600.32 205.28L1637.84 205.28L1637.84 352ZM1637.84 184.84L1600.32 184.84L1600.32 147.6L1637.84 147.6L1637.84 184.84ZM1752.08 314.76L1759.64 344.44Q1752.08 347.8 1741.16 351.16Q1730.24 354.52 1718.2 354.52Q1710.36 354.52 1703.36 352.56Q1696.64 350.6 1691.32 346.4Q1686.28 341.92 1683.2 335.2Q1680.12 328.2 1680.12 318.4L1680.12 234.12L1660.8 234.12L1660.8 205.28L1680.12 205.28L1680.12 157.68L1717.64 157.68L1717.64 205.28L1748.44 205.28L1748.44 234.12L1717.64 234.12L1717.64 305.8Q1717.64 313.64 1721.56 317Q1725.76 320.08 1731.64 320.08Q1737.52 320.08 1743.12 318.12Q1748.72 316.16 1752.08 314.76ZM1843.64 354.8Q1826 354.8 1811.72 348.92Q1797.44 342.76 1787.36 332.4Q1777.28 322.04 1771.68 308.32Q1766.36 294.6 1766.36 279.48Q1766.36 263.8 1771.68 250.08Q1777 236.08 1787.08 225.72Q1797.16 215.08 1811.44 208.92Q1826 202.76 1843.92 202.76Q1861.84 202.76 1875.84 208.92Q1890.12 215.08 1899.92 225.44Q1910 235.8 1915.04 249.52Q1920.36 263.24 1920.36 278.08Q1920.36 281.72 1920.08 285.08Q1920.08 288.44 1919.52 290.68L1806.12 290.68Q1806.96 299.36 1810.32 306.08Q1813.68 312.8 1819 317.56Q1824.32 322.32 1831.04 324.84Q1837.76 327.36 1845.04 327.36Q1856.24 327.36 1866.04 322.04Q1876.12 316.44 1879.76 307.48L1911.96 316.44Q1903.84 333.24 1885.92 344.16Q1868.28 354.8 1843.64 354.8ZM1805.56 266.32L1881.72 266.32Q1880.32 249.8 1869.4 240Q1858.76 229.92 1843.36 229.92Q1835.8 229.92 1829.08 232.72Q1822.64 235.24 1817.6 240Q1812.56 244.76 1809.2 251.48Q1806.12 258.2 1805.56 266.32ZM2030.68 203.6L2030.68 237.76Q2013.6 238.04 2000.16 244.48Q1986.72 250.64 1980.84 263.24L1980.84 352L1943.32 352L1943.32 205.28L1977.76 205.28L1977.76 236.64Q1981.68 229.08 1987 223.2Q1992.32 217.04 1998.48 212.56Q2004.64 208.08 2010.8 205.84Q2017.24 203.32 2023.12 203.32Q2026.2 203.32 2027.6 203.32Q2029.28 203.32 2030.68 203.6Z" />
          </svg>
        </a>
      </li>
      <li class="menu-toggle">
        <button onclick="toggleMenu();">&#9776;</button>
      </li>
      <li class="menu-item hidden">
        <a href="https://codeigniter.com" target="_blank" title="CodeIgniter.com">Discover</a>
      </li>
      <li class="menu-item hidden">
        <a href="https://codeigniter4.github.io/userguide/" target="_blank" title="User guide">Learn</a>
      </li>
      <li class="menu-item hidden">
        <a href="https://forum.codeigniter.com/" target="_blank" title="Forum">Discuss</a>
      </li>
      <li class="menu-item hidden">
        <a href="https://github.com/codeigniter4/CodeIgniter4/blob/master/CONTRIBUTING.md" target="_blank" title="Github">Contribute</a>
      </li>
    </ul>
  </div>

  <div class="heroe">

    <h1>Welcome to CodeIgniter <?= CodeIgniter\CodeIgniter::CI_VERSION ?></h1>

    <h2>The small framework with powerful features</h2>

  </div>

</header>

<!-- CONTENT -->

<section>

  <div class="content">

    <h2>About this page</h2>

    <p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

    <p>If you would like to edit this page you will find it located at:</p>

    <pre><code>app/Views/welcome_message.php</code></pre>

    <p>The corresponding controller for this page can be found at:</p>

    <pre><code>app/Controllers/Home.php</code></pre>

  </div>

</section>

<section>

  <div class="content">

    <h2>Go further</h2>

    <h3>
      <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><rect x='32' y='96' width='64' height='368' rx='16' ry='16' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/><line x1='112' y1='224' x2='240' y2='224' style='fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px'/><line x1='112' y1='400' x2='240' y2='400' style='fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px'/><rect x='112' y='160' width='128' height='304' rx='16' ry='16' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/><rect x='256' y='48' width='96' height='416' rx='16' ry='16' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/><path d='M422.46,96.11l-40.4,4.25c-11.12,1.17-19.18,11.57-17.93,23.1l34.92,321.59c1.26,11.53,11.37,20,22.49,18.84l40.4-4.25c11.12-1.17,19.18-11.57,17.93-23.1L445,115C443.69,103.42,433.58,94.94,422.46,96.11Z' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/></svg>
      Learn
    </h3>

    <p>The User Guide contains an introduction, tutorial, a number of "how to"
      guides, and then reference documentation for the components that make up
      the framework.</p>
    <p class="mini-buttons">
      <a href="https://codeigniter4.github.io/userguide" class="button" target="_blank">Read the User Guide</a>
    </p>

    <h3>
      <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M431,320.6c-1-3.6,1.2-8.6,3.3-12.2a33.68,33.68,0,0,1,2.1-3.1A162,162,0,0,0,464,215c.3-92.2-77.5-167-173.7-167C206.4,48,136.4,105.1,120,180.9a160.7,160.7,0,0,0-3.7,34.2c0,92.3,74.8,169.1,171,169.1,15.3,0,35.9-4.6,47.2-7.7s22.5-7.2,25.4-8.3a26.44,26.44,0,0,1,9.3-1.7,26,26,0,0,1,10.1,2L436,388.6a13.52,13.52,0,0,0,3.9,1,8,8,0,0,0,8-8,12.85,12.85,0,0,0-.5-2.7Z' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/><path d='M66.46,232a146.23,146.23,0,0,0,6.39,152.67c2.31,3.49,3.61,6.19,3.21,8s-11.93,61.87-11.93,61.87a8,8,0,0,0,2.71,7.68A8.17,8.17,0,0,0,72,464a7.26,7.26,0,0,0,2.91-.6l56.21-22a15.7,15.7,0,0,1,12,.2c18.94,7.38,39.88,12,60.83,12A159.21,159.21,0,0,0,284,432.11' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/></svg>
      Discuss
    </h3>

    <p>CodeIgniter is a community-developed open source project, with several
       venues for the community members to gather and exchange ideas.</p>
    <p class="mini-buttons">
      <a href="https://forum.codeigniter.com/" class="button" target="_blank">Visit the forum</a>
      <a href="https://codeigniterchat.slack.com/" class="button" target="_blank">Chat on Slack</a>
    </p>

    <h3>
       <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><line x1='176' y1='48' x2='336' y2='48' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/><line x1='118' y1='304' x2='394' y2='304' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/><path d='M208,48v93.48a64.09,64.09,0,0,1-9.88,34.18L73.21,373.49C48.4,412.78,76.63,464,123.08,464H388.92c46.45,0,74.68-51.22,49.87-90.51L313.87,175.66A64.09,64.09,0,0,1,304,141.48V48' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/></svg>
       Contribute
    </h3>

    <p>CodeIgniter is a community driven project and accepts contributions
       of code and documentation from the community.</p>
    <p class="mini-buttons">
      <a href="https://github.com/codeigniter4/CodeIgniter4/blob/master/CONTRIBUTING.md" class="button" target="_blank">Join us</a>
    </p>

  </div>

</section>

<!-- FOOTER: DEBUG INFO + COPYRIGHTS -->

<footer>
  <div class="environment">

    <p>Page rendered in {elapsed_time} seconds</p>

    <p>Environment: <?= ENVIRONMENT ?></p>

  </div>

  <div class="copyrights">

    <p>&copy; <?= date('Y') ?> CodeIgniter Foundation. CodeIgniter is open source project released under the MIT
      open source licence.</p>

  </div>

</footer>

<!-- SCRIPTS -->

<script>
  function toggleMenu() {
    var menuItems = document.getElementsByClassName('menu-item');
    for (var i = 0; i < menuItems.length; i++) {
      var menuItem = menuItems[i];
      menuItem.classList.toggle("hidden");
    }
  }
</script>


</body>
</html>
