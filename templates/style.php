<?php
/* @var $fontSize
 * @var $fontWeight
 * @var $headerSize
 * @var $headerWeight
 */

$css = <<<CSS
body {
	width: 100%;
	font-size: {$fontSize}px;
	font-weight: {$fontWeight};
	margin: 0;
	font-family: Arial, sans-serif;
}

@page {
	margin: 0;
}

* {
	-webkit-print-color-adjust: exact;
	max-width: 100%;
	box-sizing: border-box;
}

header {
	text-align: center;
	margin-bottom: 20px;
	width: 90%;
	margin-left: auto;
	margin-right: auto;
}

td, th {
	border-width: 0;
	border-style: solid;
	border-color: black;
}

.logo {
	width: auto;
	height: 60px;
	float: left;
	margin-right: 10px;
}

h1, h2, h3, h4, h5, h6, th {
	font-size: {$headerSize}px;
	font-weight: {$headerWeight};
}

header:after {
	content: '';
	display: block;
	width: 100%;
	clear: both;
}

table {
	width: 90%;
	border-collapse: collapse;
	font-size: inherit;
	margin-bottom: 20px;
	margin-left: auto;
	margin-right: auto;
}

h2.caption {
	width: 90%;
	display: block;
	margin-left: auto;
	margin-right: auto;
	text-align: left;
}

.info td, .info th {
	border: 1px solid black;
}

.info tfoot tr td {
	background: #000;
	color: white;
	font-size: {$headerSize}px;
	font-weight: {$headerWeight};
	text-align: center;
}

.order tfoot tr, .order thead tr, .order tbody {
	border: 1px solid black;
}

.order tfoot td:first-child, .order thead th {
	text-transform: uppercase;
	font-weight: {$headerWeight};
}

.order tfoot td:first-child, .order thead th:first-child {
	text-align: left;
	font-weight: {$headerWeight};
	font-size: {$headerSize};
}

.order tfoot td:last-child {
	text-align: center;
}

.order tbody tr:first-child td:first-child {
	text-align: left;
}

.order tbody tr:first-child td {
	text-align: center;
}

.customer tr {
	border: 1px solid black;
}

.customer .base tr td:first-child {
	text-transform: uppercase;
}

.customer .base tr td {
	text-align: center;
}

.customer .notes tr {
	border-top: 0;
}

.customer .notes tr:first-child {
	border-bottom: 0;
	border-top: 1px solid black;
}

.customer .notes tr:first-child td {
	text-transform: uppercase;
	text-align: center;
}

header h1, header h2, header h3 {
	text-align: center;
	margin: 7px;
}

header h1 {
	margin-top: 0;
	}

header h2.kitchen {
	line-height: 60px;
}

header h3 {
	margin-bottom: 0;
}

footer h4, h5 {
	text-align: center;
	margin: 0;
}

footer h5 {
	margin-top: 5px;
}

footer {
	margin-bottom: 20px;
}

table.customer:empty {
	display: none;
}

table.customer_details td, table.customer_details th {
	border: 1px solid black;
}

table.customer_details th {
	text-transform: uppercase;
}
CSS;
echo $css;
