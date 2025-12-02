<?php
	/** @var enum Visibility Realm - Marketting, Sales, Support, Administration */
	enum productVisibilityRealm: int {
		case MARKETING = 1;				# Shows up on products pages
		case SALES = 2;					# Can be added to sales quotes/orders
		case SUPPORT = 4;				# Can be selected for support parts
		case ADMINISTRATION = 8;		# Internal use only
	}