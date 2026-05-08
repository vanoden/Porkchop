<?php
	/** @var enum Visibility Realm - Marketting, Sales, Support, Administration */
	enum productVisibilityRealm: int {
		case MARKETING = 1;				# Shows up on products pages
		case SALES = 2;					# Can be added to sales quotes/orders
		case ASSEMBLY = 3;				# Can be selected as part of an assembly
		case SUPPORT = 4;				# Can be selected for support parts
		case ADMINISTRATION = 5;		# Internal use only
	}