<?php

namespace Config;

class Hostnames
{
    // List of known two-part TLDs for subdomain extraction
    public const TWO_PART_TLDS = [
        'co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'sch.uk', 'ltd.uk', 'plc.uk',
        'com.au', 'net.au', 'org.au', 'edu.au', 'gov.au', 'asn.au', 'id.au',
        'co.jp', 'ac.jp', 'go.jp', 'or.jp', 'ne.jp', 'gr.jp',
        'co.nz', 'org.nz', 'govt.nz', 'ac.nz', 'net.nz', 'geek.nz', 'maori.nz', 'school.nz',
        'co.in', 'net.in', 'org.in', 'ind.in', 'ac.in', 'gov.in', 'res.in',
        'com.cn', 'net.cn', 'org.cn', 'gov.cn', 'edu.cn',
        'com.sg', 'net.sg', 'org.sg', 'gov.sg', 'edu.sg', 'per.sg',
        'co.za', 'org.za', 'gov.za', 'ac.za', 'net.za',
        'co.kr', 'or.kr', 'go.kr', 'ac.kr', 'ne.kr', 'pe.kr',
        'co.th', 'or.th', 'go.th', 'ac.th', 'net.th', 'in.th',
        'com.my', 'net.my', 'org.my', 'edu.my', 'gov.my', 'mil.my', 'name.my',
        'com.mx', 'org.mx', 'net.mx', 'edu.mx', 'gob.mx',
        'com.br', 'net.br', 'org.br', 'gov.br', 'edu.br', 'art.br', 'eng.br',
        'co.il', 'org.il', 'ac.il', 'gov.il', 'net.il', 'muni.il',
        'co.id', 'or.id', 'ac.id', 'go.id', 'net.id', 'web.id', 'my.id',
        'com.hk', 'edu.hk', 'gov.hk', 'idv.hk', 'net.hk', 'org.hk',
        'com.tw', 'net.tw', 'org.tw', 'edu.tw', 'gov.tw', 'idv.tw',
        'com.sa', 'net.sa', 'org.sa', 'gov.sa', 'edu.sa', 'sch.sa', 'med.sa',
        'co.ae', 'net.ae', 'org.ae', 'gov.ae', 'ac.ae', 'sch.ae',
        'com.tr', 'net.tr', 'org.tr', 'gov.tr', 'edu.tr', 'av.tr', 'gen.tr',
        'co.ke', 'or.ke', 'go.ke', 'ac.ke', 'sc.ke', 'me.ke', 'mobi.ke', 'info.ke',
        'com.ng', 'org.ng', 'gov.ng', 'edu.ng', 'net.ng', 'sch.ng', 'name.ng',
        'com.pk', 'net.pk', 'org.pk', 'gov.pk', 'edu.pk', 'fam.pk',
        'com.eg', 'edu.eg', 'gov.eg', 'org.eg', 'net.eg',
        'com.cy', 'net.cy', 'org.cy', 'gov.cy', 'ac.cy',
        'com.lk', 'org.lk', 'edu.lk', 'gov.lk', 'net.lk', 'int.lk',
        'com.bd', 'net.bd', 'org.bd', 'ac.bd', 'gov.bd', 'mil.bd',
        'com.ar', 'net.ar', 'org.ar', 'gov.ar', 'edu.ar', 'mil.ar',
        'gob.cl', 'com.pl', 'net.pl', 'org.pl', 'gov.pl', 'edu.pl',
        'co.ir', 'ac.ir', 'org.ir', 'id.ir', 'gov.ir', 'sch.ir', 'net.ir',
    ];
}
