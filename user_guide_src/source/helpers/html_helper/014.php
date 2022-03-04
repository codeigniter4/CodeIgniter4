<?php

$tracks = [
    track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
    track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
];

echo video('test.mp4', 'Your browser does not support the video tag.', 'controls');

echo video(
    'http://www.codeigniter.com/test.mp4',
    'Your browser does not support the video tag.',
    'controls',
    $tracks
);

echo video(
    [
        source('movie.mp4', 'video/mp4', 'class="test"'),
        source('movie.ogg', 'video/ogg'),
        source('movie.mov', 'video/quicktime'),
        source('movie.ogv', 'video/ogv; codecs=dirac, speex'),
    ],
    'Your browser does not support the video tag.',
    'class="test" controls',
    $tracks
);
