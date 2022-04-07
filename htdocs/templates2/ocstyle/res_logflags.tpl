{if $lfSpace && ($logItem.needs_maintenance==2 || ($logItem.needs_maintenance==1 && !(isset($logItem.type) && $logItem.type==10)) || $logItem.listing_outdated==2 || ($logItem.listing_outdated==1 && !(isset($logItem.type) && $logItem.type==10)) || ($withRecommendation && $logItem.recommended))}&nbsp;{/if}
{if $logItem.needs_maintenance==2}<img src="resource2/{$opt.template.style}/images/log/16x16-needs-maintenance.png" alt="{t}The geocache needs maintenance.{/t}" title="{t}The geocache needs maintenance.{/t}" />{if $logItem.listing_outdated}&nbsp;{/if}{elseif $logItem.needs_maintenance==1 && !(isset($logItem.type) && $logItem.type==10)}<img src="resource2/{$opt.template.style}/images/log/16x16-needs-maintenance-no.png" alt="{t}The geocache is in good or acceptable condition.{/t}" title="{t}The geocache is in good or acceptable condition.{/t}" />{if $logItem.listing_outdated}&nbsp;{/if}{/if}
{if $logItem.listing_outdated==2}<img src="resource2/{$opt.template.style}/images/log/16x16-listing-outdated.png" alt="{t}The geocache description is outdated.{/t}" title="{t}The geocache description is outdated.{/t}" />{elseif $logItem.listing_outdated==1 && !(isset($logItem.type) && $logItem.type==10)}<img src="resource2/{$opt.template.style}/images/log/16x16-listing-outdated-no.png" alt="{t}The geocache description is ok.{/t}" title="{t}The geocache description is ok.{/t}" />{/if}
{if $withRecommendation && $logItem.recommended==1}  {* Ocprop: rating-star\.gif *}<img src="resource2/{$opt.template.style}/images/viewcache/rating-star.png" border="0" alt="{t}Recommended{/t}" width="16px" height="16px" />{/if}
