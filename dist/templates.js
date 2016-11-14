angular.module("app").run(["$templateCache", function($templateCache) {$templateCache.put("categoryScrollerCollectionDirective.html","<load-message message=\"!vm.categoryNames? \'Loading\': undefined\"></load-message>\r\n<category-scroller category-name=\"categoryName\" ng-repeat=\"categoryName in vm.categoryNames\"></category-scroller>");
$templateCache.put("categoryScrollerDirective.html","<div ng-if=\"vm.category.videos.length > 0\">\r\n    <h1>{{::vm.category.name}} {{vm.getLocationText()}}</h1>\r\n    <load-message message=\"vm.category? undefined: \'Loading\'\"></load-message>\r\n    <div ng-if=\"!vm.visibleVideos || vm.visibleVideos.length === 0\">No videos found</div>\r\n    <div ng-if=\"vm.visibleVideos.length > 0\" class=\"category-scroller-video-container\"  ng-swipe-right=\"vm.pageLeft()\" ng-swipe-left=\"vm.pageRight()\" ng-class=\"vm.direction\">\r\n        <a class=\"btn btn-default navigate navigate-left\" ng-click=\"vm.pageLeft()\" ng-if=\"vm.showPageLeft()\">\r\n            <span class=\"glyphicon glyphicon-chevron-left\"></span>\r\n        </a>\r\n        <video-tile video=\"video\" ng-repeat=\"video in vm.visibleVideos\" class=\"video-tile\"></video-tile>\r\n        <div class=\"btn btn-default navigate navigate-right\" ng-click=\"vm.pageRight()\" ng-if=\"vm.showPageRight()\">\r\n            <span class=\"glyphicon glyphicon-chevron-right\"></span>\r\n        </div>\r\n    </div>\r\n    <!-- add the first video as a hidden tile so that we always have a way of determine the video-tile\'s size -->\r\n    <video-tile class=\"hidden-video\" video=\"vm.category.videos[0]\"></video-tile>\r\n</div>");
$templateCache.put("episodeDirective.html","<div class=\"episode\" ng-mouseenter=\"vm.hover = true\" ng-mouseleave=\"vm.hover = false\" ng-class=\"{selected: vm.selected}\">\r\n    <div class=\"blur-screen\"></div>\r\n    <div class=\"season-episode-number\">\r\n        Season {{vm.episode.seasonNumber}}, Episode {{vm.episode.episodeNumber}}<br/>\r\n    </div>\r\n    <div class=\"title-info\" title=\"{{vm.episode.title}}\">\r\n        {{vm.title}} {{::vm.runtimeText}}\r\n    </div>\r\n    <img ng-attr-src=\"{{vm.episode.hdPosterUrl}}\" />\r\n    <a ng-if=\"vm.hover\" class=\"play\" ui-sref=\"play({videoId: vm.episode.videoId, showVideoId: vm.episode.videoId})\" title=\"Play\">\r\n        <span class=\"glyphicon glyphicon-play-circle\"></span>\r\n    </a>\r\n</div> ");
$templateCache.put("videoTileDirective.html","<a ui-sref=\"videoInfo({videoId: vm.video.videoId})\">\r\n    <span ng-if=\"!vm.video.posterModifiedDate\" class=\"noPosterText\">{{vm.video.title}}</span>\r\n    <img class=\"poster\" ng-attr-src=\"{{vm.video.hdPosterUrl}}\">\r\n</a>");
$templateCache.put("addNewMediaItem.html","<form ng-submit=\"vm.addNewMediaItem()\">\r\n    <div class=\"container\">\r\n        <h2>Add new media item</h2>\r\n\r\n        <label><b>Video Source: </b>\r\n            <select class=\"form-control\" ng-model=\"vm.newMediaItem.videoSourceId\" ng-options=\"source.id as source.location for source in vm.videoSources\">\r\n                <option value=\"\">Detect automatically</option>\r\n            </select>\r\n        </label>\r\n        <br/>\r\n        <label><b>Path to folder containing new videos, or the full path to the new video</b>\r\n            <input type=\"text\" ng-model=\"vm.newMediaItem.path\" class=\"form-control\" />\r\n        </label>\r\n        <br/>\r\n        <button class=\"btn btn-success center-block form-control\">Add</button>\r\n        <load-message message=\"vm.loadMessage\"></load-message>\r\n    </div>\r\n</form>");
$templateCache.put("admin.html","<div class=\"container\">\r\n    <div class=\"row\">\r\n        <div class=\"col-md-7\">\r\n            <br/>\r\n            <a ng-show=\"!globals.generateLibraryIsPending\" ng-click=\"vm.generateLibrary()\" class=\"btn btn-default\">Generate/Update library</a>\r\n            <span ng-show=\"globals.generateLibraryIsPending\"><span class=\"loading\"></span> Generating Library </span>\r\n            <br/>\r\n            <br/>\r\n            <a ui-sref=\"videoSources\" class=\"btn btn-default\">Manage Video Sources</a>\r\n            <br/>\r\n            <br/>\r\n            <a ng-show=\"!globals.fetchMissingMetadataIsPending\" class=\"btn btn-default\" ng-click=\"vm.fetchMissingMetadata()\">Fetch Missing Metadata</a>\r\n            <span ng-show=\"globals.fetchMissingMetadataIsPending\"><span class=\"loading\"></span>Fetching missing metadata</span>\r\n            <br/>\r\n            <br/>\r\n            <a class=\"btn btn-default\" ui-sref=\"addNewMediaItem\">Add new item to library</a>\r\n            <br/>\r\n            <br/>\r\n            <a class=\"btn btn-default\" ng-click=\"vm.clearCache()\">Clear Cache</a>\r\n            <br/>\r\n            <br/>\r\n            <div ng-show=\"!globals.checkForUpdatesIsPending\">\r\n                <a class=\"btn btn-default\" ng-click=\"vm.updateApplication()\">Check for and install updates</a>\r\n\r\n                <br/>\r\n                <label><input type=\"checkbox\" name=\"force\" value=\"true\"/> Force latest update to install</label>\r\n                <div ng-if=\"vm.serverVersionNumber\">\r\n                    Currently installed version: {{vm.serverVersionNumber}}\r\n                </div>\r\n            </div>\r\n            <span ng-show=\"globals.checkForUpdatesIsPending\"><span class=\"loading\"></span>Checking for and installing updates</span>\r\n\r\n            <div id=\"generateLibraryModal\" class=\"modal fade\">\r\n                <div class=\"modal-dialog\">\r\n                    <div class=\"modal-content\">\r\n                        <div class=\"modal-header\">\r\n                            <button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button>\r\n                            <h4 class=\"modal-title\"></h4>\r\n                        </div>\r\n                        <div class=\"modal-body\">\r\n                            <p></p>\r\n                        </div>\r\n                        <div class=\"modal-footer\">\r\n                            <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>\r\n                        </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        <div class=\"col-md-5\">\r\n            <h3>Summary</h3>\r\n            <b>Video Count:</b>\r\n            {{vm.videoCounts.videoCount}}\r\n            <br/>\r\n            <b>Movie Count:</b>\r\n            {{vm.videoCounts.movieCount}}\r\n            <br/>\r\n            <b>Tv Show Count:</b>\r\n            {{vm.videoCounts.tvShowCount}}\r\n            <br/>\r\n            <b>Tv Episode Count:</b>\r\n            {{vm.videoCounts.tvEpisodeCount}}\r\n            <br/>\r\n        </div>\r\n    </div>\r\n</div>");
$templateCache.put("editVideoSource.html","<form name=\"vm.form\" \r\n      novalidate \r\n      class=\"edit-video-source-container form\" \r\n      ng-class=\"{\r\n            \'disabled-background\': vm.isLoading\r\n        }\"\r\n      ng-submit=\"vm.save()\"\r\n      >\r\n    <div class=\"row\">\r\n        <div class=\"col-xs-3\">Base File Path: </div>\r\n        <div class=\"col-xs-9\">                  \r\n            <input type=\"text\" \r\n                   name=\"path\"\r\n                   ng-model=\"vm.videoSource.location\" \r\n                   ng-disabled=\"vm.isLoading\" \r\n                   class=\"form-control\" \r\n                   placeholder=\"ex: c:/videos/Movies/\"\r\n                   required \r\n                   path-exists-validator\r\n                   >\r\n            <span class=\"text-danger\" ng-show=\"vm.form.path.$error.pathExists\">\r\n                Path does not exist on the server\r\n            </span>\r\n            <span class=\"text-success\" ng-show=\"vm.form.path.$valid\">\r\n                Valid server path\r\n            </span>\r\n            <span ng-if=\"vm.form.path.$pending\">\r\n                <span class=\"loading\"></span> Validating\r\n            </span>\r\n            <br/>\r\n            <b>*NOTE: </b>This is a file path that the SERVER can see, not your local computer\r\n        </div>\r\n    </div>\r\n    <br/>\r\n    <div id=\"baseUrlRow\" class=\"row\" style=\"display:block;\">\r\n        <div class=\"col-xs-3\">Base URL: </div>\r\n        <div class=\"col-xs-9\">                  \r\n            <input type=\"text\" \r\n                   name=\"baseUrl\"\r\n                   ng-model=\"vm.videoSource.baseUrl\" \r\n                   ng-disabled=\"vm.isLoading\"  \r\n                   class=\"form-control\" \r\n                   placeholder=\"ex: http://localhost/videos/movies/\"\r\n                   url-exists-validator\r\n                   required/>\r\n            <span ng-if=\"form.baseUrl.$pending\">\r\n                <span class=\"loading\"></span> Validating\r\n            </span>\r\n            <span class=\"text-danger\" ng-show=\"!vm.form.baseUrl.$valid && !vm.form.$pristine\">\r\n                URL is invalid\r\n            </span>\r\n            <span class=\"text-success\" ng-show=\"vm.form.baseUrl.$valid\">\r\n                URL is valid\r\n            </span>\r\n            <br/>\r\n            <b>*NOTE: </b>This is a url that already exists. You must serve the videos over http using your web server.\r\n            <br/>\r\n            <br/>\r\n        </div>\r\n    </div>\r\n    <div class=\"row\">\r\n        <br/>\r\n        <div class=\"col-xs-3\">Security Type: </div>\r\n        <div class=\"col-xs-9\">\r\n            <label>\r\n                <input type=\"radio\" name=\"securityType\" ng-model=\"vm.videoSource.securityType\" ng-disabled=\"vm.isLoading\" ng-attr-value=\"{{enums.securityType.public}}\">\r\n                No Security</label>\r\n            <!-- &nbsp;\r\n            <input type=\"radio\" id=\"securityTypePrivate\" name=\"securityType\"  value=\"<?php echo Enumerations::SecurityType_LoginRequired; ?>\">\r\n            <label for=\"securityTypePrivate\">Login Required</label>-->\r\n            <br/>\r\n            <br/>\r\n\r\n        </div>\r\n    </div>\r\n    <div class=\"row\">\r\n        <div class=\"col-xs-3\">Media Type: </div>\r\n        <div class=\"col-xs-9\">\r\n            <label>\r\n                <input type=\"radio\" \r\n                       ng-model=\"vm.videoSource.mediaType\" \r\n                       required \r\n                       ng-disabled=\"vm.isLoading\" \r\n                       name=\"mediaType\" \r\n                       ng-attr-value=\"{{enums.mediaType.movie}}\">\r\n                Directory full of movies\r\n            </label>\r\n            &nbsp;<br/>\r\n            <label >\r\n                <input type=\"radio\" \r\n                       ng-disabled=\"vm.isLoading\" \r\n                       ng-model=\"vm.videoSource.mediaType\" \r\n                       name=\"mediaType\" \r\n                       ng-attr-value=\"{{enums.mediaType.show}}\">\r\n                Directory full of Tv Shows (Each in its own tv show folder)\r\n            </label>\r\n        </div>\r\n    </div>\r\n    <br/>\r\n    <div class=\"row\">\r\n        <div class=\"col-xs-12 text-center\" >\r\n            <span ng-if=\"vm.isLoading\">\r\n                <span class=\"loading\"></span>Loading video source\r\n            </span>\r\n            <span ng-if=\"vm.isSaving\">\r\n                <span class=\"loading\"></span>Saving\r\n            </span>\r\n            <a class=\"btn btn-warning\" ng-click=\"vm.reset()\">Cancel</a>\r\n            <button \r\n                type=\"submit\"\r\n                ng-if=\"vm.videoSource && !vm.isLoading\" \r\n                class=\"btn btn-success\" \r\n                ng-disabled=\"!vm.form.$valid\"\r\n                >\r\n                {{!vm.videoSource.id?\'Create new\': \'Save updates\'}}\r\n            </button>\r\n        </div>\r\n    </div>\r\n</form>");
$templateCache.put("fetchByTitle.html","");
$templateCache.put("home.html","<!--<div infinite-scroll=\'vm.loadMore()\' infinite-scroll-distance=\'1\' ng-if=\'vm.allVideos.length > 0\'>\r\n    <video-tile video=\"video\" ng-repeat=\"video in vm.currentlyLoadedVideos\"></video-tile>\r\n</div>-->\r\n\r\n<category-scroller-collection></category-scroller-collection>");
$templateCache.put("metadataFetcher.html","<div class=\"container\">\r\n    <a ui-sref=\"videoInfo({videoId: vm.videoId})\">&lt; Back to video</a>\r\n    <br/>\r\n    <div class=\"row\">\r\n        <div class=\"col-sm-2\"><b>Path: </b></div>\r\n        <div class=\"col-sm-10\">{{vm.video.path}}</div>\r\n    </div>\r\n    <div class=\"row\">\r\n        <div class=\"col-sm-2\"><b>Source Path: </b></div>\r\n        <div class=\"col-sm-10\">{{vm.video.sourcePath}}</div>\r\n    </div>\r\n\r\n    <div class=\"row\">\r\n        <div class=\"col-sm-2\"><b>Media Type: </b></div>\r\n        <div class=\"col-sm-10\">{{vm.video.mediaType}}</div>\r\n    </div>\r\n    <br/>   \r\n    <!-- \"Search By\" row -->\r\n    <div id=\"searchByRow\" class=\"row\">\r\n        <div class=\"col-sm-2\">\r\n            <b>Search by: </b>\r\n        </div>\r\n        <div class=\"col-sm-10\">\r\n            <label class=\"non-bold\">\r\n                <input type=\"radio\" \r\n                       name=\"searchBy\"  \r\n                       ng-model=\"vm.searchBy\" \r\n                       value=\"title\" \r\n                       ng-init=\"vm.searchBy = \'title\'\">\r\n                Title\r\n            </label>\r\n            <label class=\"non-bold\">\r\n                <input type=\"radio\" \r\n                       name=\"searchBy\" \r\n                       ng-model=\"vm.searchBy\" \r\n                       value=\"onlineVideoId\">\r\n                {{vm.video.mediaType === enums.mediaType.movie?\'TMDB ID\': \'TVDB ID\'}}\r\n            </label>        \r\n        </div>       \r\n    </div>\r\n    <form ng-submit=\"vm.search()\">\r\n        <div class=\"row\">\r\n            <div class=\"col-sm-2\">\r\n                <b>{{vm.textboxLabel}}: </b>\r\n            </div>       \r\n            <div class=\"col-sm-8\">\r\n                <label>\r\n                    <input class=\"form-control\" type=\"text\" ng-model=\"vm.searchValue\" />\r\n                </label>\r\n            </div>    \r\n            <div class=\"col-sm-2 text-center\">\r\n                <label>\r\n                    <button type=\"submit\" ng-if=\"!vm.isSearching\" class=\"btn btn-primary form-control\">Search</button>\r\n                </label>\r\n            </div>   \r\n        </div>\r\n    </form>\r\n    <div class=\"row\"  ng-if=\"vm.isSearching\">\r\n        <div class=\"col-sm-12\">\r\n            <span class=\"loading\"></span>&nbsp;Fetching video metadata\r\n        </div>\r\n    </div>\r\n    <br/>\r\n    <!-- Search results -->\r\n    <div id=\"metadataSearchResults\">\r\n        <div class=\"loading-metadata\"  ng-show=\"vm.metadataIsBeingFetched\">\r\n            <h3>\r\n                <span class=\"loading\"></span>\r\n                Updating video with selected metadata\r\n            </h3>\r\n        </div>\r\n        <div ng-hide=\"vm.metadataIsBeingFetched\">\r\n            <div ng-if=\"vm.metadataResults\"><h2>Select the correct video from the results below</h2></div>\r\n            <div ng-if=\"vm.metadataResults.length === 0\">No results found</div>\r\n            <div ng-repeat=\"video in vm.metadataResults\" class=\"metadata-tile\" ng-click=\"vm.fetchMetadataByOnlineVideoId(video.onlineVideoId);\">\r\n                <div class=\"row\">\r\n                    <div class=\"col-sm-3\">\r\n                        <div class=\"video-tile\">\r\n                            <img ng-attr-src=\"{{video.posterUrl}}\">\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"col-sm-9\">\r\n                        <div class=\"text-area\">\r\n                            <b>Title: </b>{{video.title}}<br/>\r\n                            <b>MPAA: </b>{{video.mpaa}}<br/>\r\n                            <span ng-if=\"video.mediaType === enums.mediaType.movie\">\r\n                                <b>TMDB ID: </b>\r\n                                <a target=\"_blank\" href=\"https://www.themoviedb.org/movie/{{video.onlineVideoId}}\">\r\n                                    {{video.onlineVideoId}}\r\n                                </a>\r\n                            </span>\r\n                            <span ng-if=\"video.mediaType === enums.mediaType.show\">\r\n                                <b>TVDB ID: </b>\r\n                                <a target=\"_blank\" href=\"http://thetvdb.com/?tab=series&id={{video.onlineVideoId}}\">\r\n                                    {{video.onlineVideoId}}\r\n                                </a>\r\n                            </span>\r\n                            <span ng-if=\"video.mediaType === enums.mediaType.episode\">\r\n                                <b>TVDB ID: </b>\r\n                                <a target=\"_blank\" href=\"http://thetvdb.com/?tab=episode&id={{video.onlineVideoId}}\">\r\n                                    {{video.onlineVideoId}}\r\n                                </a>\r\n                            </span>\r\n                            <br/>\r\n                            <b>Plot: </b>\r\n                            {{video.plot| limitTo: 500}}<span ng-if=\"video.plot.length > 500\">...</span>\r\n                        </div>        \r\n                    </div>      \r\n                </div>        \r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>");
$templateCache.put("navbar.html","<nav id=\"mainNavbar\" class=\"navbar navbar-inverse\" role=\"navigation\" ng-controller=\"NavbarController as vm\">\r\n    <div class=\"container-fluid\">\r\n        <!-- Brand and toggle get grouped for better mobile display -->\r\n        <div class=\"navbar-header\">\r\n            <button type=\"button\" class=\"navbar-toggle collapsed\" ng-click=\"vm.toggleNavbar()\" >\r\n                <span class=\"sr-only\">Toggle navigation</span>\r\n                <span class=\"icon-bar\"></span>\r\n                <span class=\"icon-bar\"></span>\r\n                <span class=\"icon-bar\"></span>\r\n            </button>\r\n            <a class=\"navbar-brand\" ui-sref=\"home\" ng-click=\"vm.hideNavbar()\">\r\n                <img src=\"assets/img/logo.png\" style=\"height:20px;display:inline;\">&nbsp;Plum Media Center\r\n            </a>\r\n        </div>\r\n        <div class=\"collapse navbar-collapse\" collapse=\"!vm.navbarIsOpen\">\r\n            <ul class=\"nav navbar-nav\">\r\n                <li id=\"browseNav\" ng-click=\"vm.hideNavbar()\"><a ui-sref=\"home\">Browse</a></li>\r\n                <li id=\"adminNav\" ng-click=\"vm.hideNavbar()\"><a ui-sref=\"admin\">Admin</a></li>\r\n            </ul>\r\n\r\n            <ul class=\"nav navbar-nav navbar-right\">\r\n                <li>\r\n                    <form class=\"navbar-form navbar-left\" role=\"search\" ng-submit=\"vm.search()\">\r\n                        <div class=\"form-group\">\r\n                            <input name=\"s\" type=\"text\" \r\n                                   class=\"form-control\"  \r\n                                   ng-model=\"vm.searchTerm\" \r\n                                   placeholder=\"Search\" \r\n                                   autocomplete=\"off\"\r\n                                   />\r\n                        </div>\r\n                        <button type=\"submit\" class=\"btn btn-primary form-control\">Search</button>\r\n                    </form>\r\n                </li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</nav>");
$templateCache.put("play.html","<div class=\"fill\" style=\"background-color:black;\">\r\n<a class=\"play-back-button\" ui-sref=\"videoInfo({videoId: vm.showVideoId ? vm.showVideoId: vm.videoId})\">&lt;Back</a>\r\n<jwplayer video-id=\'vm.videoId\'></jwplayer>\r\n</div>\r\n");
$templateCache.put("search.html","<h1 class=\"text-center full-width\">Search results for \"{{vm.searchTerm}}\"</h1>\r\n<load-message message=\"!vm.allVideos? \'Loading\': undefined\"></load-message>\r\n<div infinite-scroll=\'vm.loadMore()\' infinite-scroll-distance=\'2\' ng-if=\'vm.allVideos.length > 0\'>\r\n    <video-tile video=\"video\" ng-repeat=\"video in vm.currentlyLoadedVideos track by video.videoId\"></video-tile>\r\n</div>\r\n");
$templateCache.put("videoInfo.html","<div class=\"container\">\r\n    <a ng-if=\"vm.video.mediaType === enums.mediaType.episode\" ng-click=\"vm.navigateToShow()\">&lt;Back to Show</a>\r\n    <div class=\"row \" style=\"margin-top:5px;\">\r\n        <div class=\"col-md-3 text-center\">\r\n            <div id=\"videoInfoPosterColumn\">\r\n                <a ng-attr-href=\"{{vm.video.posterUrl}}\">\r\n                    <img ng-attr-src=\"{{vm.video.hdPosterUrl}}\" class=\"full-width\">\r\n                </a>\r\n                <progressbar title=\"progress\" class=\"margin\" style=\"background-color: grey;margin-top:5px;\" type=\"{{vm.getProgressPercentType()}}\" value=\"vm.progressPercent < 15? 15: vm.progressPercent \" max=\"100\"><span>{{vm.progressPercent}}%</progressbar>\r\n                <a class=\"btn btn-default full-width margin\" ui-sref=\"metadataFetcher({videoId: vm.video.videoId})\">Fetch Metadata</a>\r\n                <a class=\"btn btn-default full-width margin\" ng-click=\"vm.scanForNewMedia()\">\r\n                    <span class=\"glyphicon glyphicon-refresh\"></span>\r\n                    Scan for New Media\r\n                </a>\r\n                <load-message message=\"vm.loadMessage\"></load-message>\r\n                <a ui-sref=\"play(vm.video)\" class=\"btn btn-primary full-width margin\">\r\n                    <span class=\"glyphicon glyphicon-play\"></span>&nbsp;Play\r\n                </a>\r\n            </div>\r\n        </div>\r\n        <div class=\"col-md-9\">\r\n            <h1 class=\"text-center\">{{vm.video.title}}</h1>\r\n            <div class=\"text-center full-width\">\r\n                <b>{{vm.video.mpaa}}</b>&nbsp;&nbsp;               \r\n                <b>{{vm.video.year}}</b>\r\n\r\n            </div>\r\n            <br/> <br/>{{vm.video.plot}}\r\n            <div ng-if=\"vm.nextEpisode\">\r\n                <br/>\r\n                <h2>Next Episode</h2>\r\n                <div>\r\n                    <episode episode=\"vm.nextEpisode\" ng-if=\"vm.nextEpisode\"></episode>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>\r\n<div class=\"row\" ng-if=\"vm.episodes\"> \r\n    <div class=\"col-xs-12\">\r\n        <h1>Episodes</h1>\r\n        <episode episode=\"episode\" ng-repeat=\"episode in vm.episodes\" selected=\"episode.videoId === vm.nextEpisode.videoId\"></episode>\r\n    </div>\r\n</div>\r\n<!--    <div class=\"row \" ng-if=\"vm.episodes\">\r\n        <div class=\"col-md-5\" style=\"border:0px solid red;\">\r\n            <table class=\'table\'> \r\n                <tr>\r\n                    <th>Episode</th>\r\n                    <th style=\'display:none;\'>VID</th>\r\n                    <th></th>\r\n                    <th style=\'display:none;\'>Add To Playlist</th>\r\n                    <th>Title</th>\r\n                    <th>Progress</th>\r\n                </tr>\r\n                <tr ng-repeat=\"episode in vm.episodes\" \r\n                    ng-class=\"{\r\n                            \'nextEpisodeRow\'\r\n                            : episode.videoId === vm.nextEpisode.videoId, \'selected\': vm.selectedEpisode.videoId === episode.videoId}\" \r\n                    ng-click=\"vm.selectedEpisode = episode\"\r\n                    class=\"episodeRow\"\r\n                    style=\"border:1px solid black;\">\r\n                    <td class=\"transparent\">{{episode.episodeNumber}}</td>\r\n                    <td class=\"transparent\" style=\'display:none;\'>{{episode.videoId}}</td>\r\n                    <td class=\"transparent\">\r\n                        <a class=\"btn btn-primary btn-sm\" style=\"display:block;\" ui-sref=\"play({videoId: episode.videoId, showVideoId: vm.videoId})\" title=\"Play\">  \r\n                            <span class=\"glyphicon glyphicon-play\"></span>\r\n                        </a>\r\n                    </td>\r\n                    <td class=\"transparent\">{{episode.title}}</td>\r\n                    <td class=\"transparent\">\r\n                        <div class=\"progressbar\" ng-if=\"episode.percentWatched !== undefined\">\r\n                            <div class=\"percentWatched\" ng-attr-style=\"{{\'width:\' + episode.percentWatched + \'%\'}}\">\r\n                            </div>\r\n                            <div class=\"percentWatchedText\">{{episode.percentWatched}}\r\n                            </div>\r\n                        </div>\r\n                        <div ng-if=\"episode.percentWatched === undefined\">\r\n\r\n                        </div>\r\n                    </td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        <div class=\"col-md-7\" style=\"border:0px solid red;\">\r\n            <div id=\"episodeInfo\" class=\"shadow\" ng-class=\"{\r\n                    \'hide\'\r\n                    : !vm.selectedEpisode}\">\r\n                <h1 id=\"title\" style=\"text-align:center;\"></h1>\r\n                <img align=\"right\" id=\"episodePoster\" ng-attr-src=\"{{vm.selectedEpisode.sdPosterUrl}}\"/>\r\n                <p>Season {{vm.selectedEpisode.seasonNumber}} Episode {{vm.selectedEpisode.episodeNumber}}\r\n                    <br/><b>Rating: {{vm.selectedEpisode.mpaa}} </b>\r\n                    <br/><b>Release Date: {{vm.selectedEpisode.releaseDate}}\r\n                </p>\r\n                <span style=\"font-weight:normal;\">{{vm.selectedEpisode.plot}}</span>\r\n            </div>\r\n        </div>\r\n    </div>-->\r\n\r\n");
$templateCache.put("videoSources.html","<br/>    \r\n<br/>\r\n<a class=\"btn btn-success\" ui-sref=\"editVideoSource({id:0})\">Add New Source</a>\r\n<br/> \r\n<br/>\r\n<table class=\"table table-hover table-bordered\">\r\n    <thead>\r\n        <tr>\r\n            <th>Location</th>\r\n            <th>Media Type</th>\r\n            <th>Security Type</th>\r\n            <th>Base URL</th>\r\n            <th></th>\r\n            <th></th>\r\n        </tr>\r\n    </thead>\r\n    <tbody>\r\n        <tr class=\"pointer\" ng-repeat=\"videoSource in vm.videoSources\">\r\n            <td>{{videoSource.location}}</td>\r\n            <td>{{videoSource.mediaType}}</td>\r\n            <td>{{videoSource.securityType}}</td>\r\n            <td><a href=\'{{videoSource.baseUrl}}\'>{{videoSource.baseUrl}}</a></td>\r\n            <td class=\"text-center\">\r\n                <a class=\"btn btn-primary btn-sm editSource\" title=\"Edit\" ui-sref=\"editVideoSource({id: videoSource.id})\">\r\n                    <span class=\"glyphicon glyphicon-edit\"></span>\r\n                </a>\r\n            </td>\r\n            <td class=\"text-center\">\r\n                <button class=\"btn btn-danger btn-sm deleteSource\" title=\"Delete this video source\" confirm-message=\"\'Are you sure you want to delete this video source?\'\" confirm=\"vm.deleteVideoSource(videoSource.id)\">\r\n                    <span class=\"glyphicon glyphicon-trash\"></span>\r\n                </button>\r\n            </td>\r\n        </tr>\r\n        <?php } ?>\r\n    </tbody>\r\n</table>\r\n\r\n\r\n<div id=\"newSourceModal\" class=\"modal\" ng-class=\"{show: $state.includes(\'editVideoSource\')}\">\r\n    <div class=\"modal-dialog\">\r\n        <div class=\"modal-content\">\r\n            <div class=\"modal-header\">\r\n                <a class=\"close\" ui-sref=\"videoSources\"><span aria-hidden=\"true\">&times;</span></a>\r\n                <h4 class=\"modal-title\">Video Source</h4>\r\n            </div>\r\n            <div class=\"modal-body\" style=\"padding:0px; margin:0px;\">\r\n                <div ui-view></div>\r\n            </div>\r\n            <div class=\"modal-footer\">\r\n                <a class=\"btn btn-default\" ui-sref=\"videoSources\" ng-click=\"vm.refresh()\">Close</a>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>");}]);