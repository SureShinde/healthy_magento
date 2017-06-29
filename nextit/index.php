<?php
  /*
   *	Created 10/24/2013 by Andrew Versalle
   *	This file prevents directory listings and redirects uses to the homepage
   */
  
  header("Location: /"); /* Redirect browser */
  
  /* Make sure that code below does not get executed when we redirect. */
  exit;
?>