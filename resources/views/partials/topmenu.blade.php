<div class="menu">
    <ul >

       

                    <li {{{ (Request::is('*/overview') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@show',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-bars"></i></span>
                            <span class="text">Overview</span>
                        </a>
                    </li>
                    
                    @if( Gate::check('analytics') || Gate::check('view_analytics')  || Gate::check('edit_analytics'))
                    <li {{{ (Request::is('*/analytics') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\AnalyticsController@index',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-chart-pie"></i></span><span class="text">Analytics</span>
                        </a>
                    </li>
                    @endif

                @if( Gate::check('dns') || Gate::check('view_dns')  || Gate::check('edit_dns'))
                    
                    @if($zone->cfaccount_id!=0)
                   <li {{{ (Request::is('*/dns') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\DnsController@index',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-sitemap"></i></span><span class="text">DNS</span>
                        </a>
                    </li>`
                    @endif
                    @endif
                     @if( Gate::check('crypto') || Gate::check('view_crypto')  || Gate::check('edit_crypto'))
                     @if($zone->cfaccount_id!=0)
                     <li {{{ (Request::is('*/crypto') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@crypto',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-lock"></i></span><span class="text">Crypto</span>
                        </a>
                    </li> 
                    @endif
                    @endif
                     @if( Gate::check('firewall') || Gate::check('view_firewall')  || Gate::check('edit_firewall'))
                    <li {{{ (Request::is('*/firewall') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\FirewallController@index',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-shield-alt"></i></span><span class="text">Firewall</span>
                        </a>
                    </li> @endif
                    
               <!--      <li>
                        <a data-toggle="tab" href="#settings">
                            <span class="fa fa-user-secret"></span>
                            <div class="text-center">Access</div>
                        </a>
                    </li> -->
                     @if( Gate::check('speed') || Gate::check('view_speed')  || Gate::check('edit_speed'))
                    <li {{{ (Request::is('*/performance') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@performance',Request::segment(2))}}">
                           <span class="icon"><i class="fas fa-bolt"></i></span><span class="text">Speed</span>
                        </a>
                    </li> @endif
                     @if( Gate::check('caching') || Gate::check('view_caching')  || Gate::check('edit_caching'))
                    <li {{{ (Request::is('*/caching') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@caching',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-server"></i></span><span class="text">Caching</span>
                        </a>
                    </li> @endif

                    @if( Gate::check('pagerule') || Gate::check('view_pagerule')  || Gate::check('edit_pagerule'))
                    @if($zone->cfaccount_id!=0)
                    <li {{{ (Request::is('*/pagerules') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@pageRules',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-filter"></i></span><span class="text">Page Rules</span>
                        </a>
                    </li> 
                    @endif
                    @endif

                    @if( Gate::check('loadbalancer') || Gate::check('view_loadbalancer')  || Gate::check('edit_loadbalancer'))
                    @if($zone->cfaccount_id!=0)
                    <li {{{ (Request::is('*/loadbalancers') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@loadBalancers',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-filter"></i></span><span class="text">LoadBalancers</span>
                        </a>
                    </li> 
                    @endif
                    @endif


                    @if( Gate::check('seo') || Gate::check('view_seo')  || Gate::check('edit_seo'))
                    @if($zone->cfaccount_id==0)
                    <li {{{ (Request::is('*/seo') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@seo',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-globe"></i></span><span class="text">SEO</span>


                        </a>
                    </li> 
                    @endif
                    @endif

                    @if( Gate::check('origin') || Gate::check('view_origin')  || Gate::check('edit_origin'))
                    @if($zone->cfaccount_id==0)
                    <li {{{ (Request::is('*/origin') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@origin',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                            <span class="text">Origin</span>
                        </a>
                    </li> 
                    @endif
                    @endif
                    

                     @if( Gate::check('splogs') || Gate::check('view_splogs')  || Gate::check('edit_splogs'))
                     @if($zone->cfaccount_id==0 AND $zone->els==1)

                    <li {{{ (Request::is('*/logs') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\AnalyticsController@spLogs',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-server"></i></span><span class="text">Logs Analysis</span>
                        </a>
                    </li> @endif
                    @endif

                 <!--    <li class="">
                        <a data-toggle="tab" href="#information">
                            <span class="glyphicon glyphicon-filter"></span>
                            <div class="text-center">Page Rules</div>
                        </a>
                    </li> -->
                         @if( Gate::check('network') || Gate::check('view_network')  || Gate::check('edit_network'))
                         @if($zone->cfaccount_id!=0)
                    <li {{{ (Request::is('*/network') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@network',Request::segment(2))}}">
                           <span class="icon"><i class="fas fa-map-marker-alt"></i></span><span class="text">Network</span>
                        </a>
                    </li> 
                    @endif
                    @endif
                    
                   <!--  <li>
                        <a data-toggle="tab" href="#email">
                            <span class="fa fa-list"></span>
                            <div class="text-center">Traffic</div>
                        </a>
                    </li> -->
                     @if( Gate::check('scrape') || Gate::check('view_scrape')  || Gate::check('edit_scrape'))
                     @if($zone->cfaccount_id!=0)
                     <li {{{ (Request::is('*/content-protection') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@contentProtection',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-file-alt"></i></span><span class="text">Scrape</span>
                        </a>
                    </li>
                    @endif

                    @endif

                 <!--   @if( Gate::check('scrape') || Gate::check('view_scrape')  || Gate::check('edit_scrape'))
                     @if($zone->cfaccount_id!=0)
                     <li {{{ (Request::is('*/content-zone') ? 'class=active' : '') }}}>
                        <a  href="{{action('Admin\ZoneController@contentZone',Request::segment(2))}}">
                            <span class="icon"><i class="fas fa-file-alt"></i></span><span class="text">Zones</span>
                        </a>
                    </li>
                    @endif
 @endif
 
 -->
                     

                </ul>

</div>