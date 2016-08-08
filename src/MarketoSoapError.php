<?php

namespace CodeCrafts\MarketoSoap;

/**
 * Marketo errors.
 */
class MarketoSoapError {

  const ERR_SEVERE_INTERNAL_ERROR = '10001';
  const ERR_INTERNAL_ERROR = '20011';
  const ERR_REQUEST_NOT_UNDERSTOOD = '20012';
  const ERR_ACCESS_DENIED = '20013';
  const ERR_AUTH_FAILED = '20014';
  const ERR_REQUEST_LIMIT_EXCEEDED = '20015';
  const ERR_REQ_EXPIRED = '20016';
  const ERR_INVALID_REQ = '20017';
  const ERR_BAD_ENCODING = '20018';
  const ERR_UNSUPPORTED_OP = '20019';

  const ERR_LEAD_KEY_REQ = '20101';
  const ERR_LEAD_KEY_BAD = '20102';
  const ERR_LEAD_NOT_FOUND = '20103';
  const ERR_LEAD_DETAIL_REQ = '20104';
  const ERR_LEAD_ATTRIB_BAD = '20105';
  const ERR_LEAD_SYNC_FAILED = '20106';
  const ERR_ACTIVITY_KEY_BAD = '20107';
  const ERR_PARAMETER_REQ = '20109';
  const ERR_PARAMETER_BAD = '20110';
  const ERR_LIST_NOT_FOUND = '20111';
  const ERR_CAMP_NOT_FOUND = '20113';
  const ERR_BAD_PARAMETER = '20114';
  const ERR_BAD_STREAM_POS = '20122';
  const ERR_STREAM_AT_END = '20123';

}
