angular.module('app').service('Video', ['$http', '$q', '_', function ($http, $q, _) {
    function Video() {

    }

    Video.search = function (searchTerm) {
        var deferred = $q.defer();
        $http.get('api/GetSearchResults.php', { params: { q: searchTerm } }).success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    };

    Video.getAll = function () {
        return $q(function (resolve, reject) {
            $http.get('api/GetLibrary.php').success(function (data) {
                resolve(data);
            });
        });
    };

    Video.getById = function (id) {
        var deferred = $q.defer();
        if (!_.isNumber(id)) {
            deferred.reject();
        } else {
            $http.get('api/GetVideo.php?videoId=' + id).success(function (data) {
                deferred.resolve(data);
            }).error(function () {
                deferred.reject(data);
            });
        }
        return deferred.promise;
    };

    Video.getEpisodes = function (showId) {
        var deferred = $q.defer();
        $http.get('api/GetTvEpisodes.php?videoId=' + showId).success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    };

    Video.getNextEpisode = function (showId) {
        var deferred = $q.defer();
        $http.get('api/GetNextEpisode.php?videoId=' + showId).success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    };

    /**
     * Get how much percentage watched this video is
     * @param {type} videoId
     * @returns {$q@call;defer.promise}
     */
    Video.getProgressPercent = function (videoId) {
        var deferred = $q.defer();
        $http.get('api/GetVideoProgressPercent.php', { params: { videoId: videoId } })
            .success(function (result) {
                deferred.resolve(result.percent);
            })
            .error(deferred.reject);
        return deferred.promise;
    };

    /**
     * Get how much percentage watched each video is
     * @param {type} videoId
     * @returns {$q@call;defer.promise}
     */
    Video.getProgressPercentMultiple = function (videoIds) {
        videoIds = _.isArray(videoIds) ? videoIds : [];
        var deferred = $q.defer();
        $http.get('api/GetVideoProgressPercentMultiple.php', {
            params: {
                videoIds: videoIds.join(',')
            }
        }).success(function (result) {
            deferred.resolve(result);
        }).error(deferred.reject);
        return deferred.promise;
    };

    /**
     * Get the number of seconds into a video the current user is.
     * @param {type} videoId
     * @returns {$q@call;defer.promise}
     */
    Video.getProgress = function (videoId) {
        var deferred = $q.defer();
        $http.get('api/GetVideoProgress.php', {
            params: {
                videoId: videoId
            }
        }).success(function (data) {
            deferred.resolve(data.startSeconds);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    }

    Video.setProgress = function (videoId, seconds, isFinished) {
        isFinished = isFinished === true ? true : false;

        var deferred = $q.defer();
        $http.get('api/SetVideoProgress.php', {
            params: {
                videoId: videoId,
                seconds: seconds,
                finished: isFinished
            }
        }).success(function (data) {
            if (data.success) {
                deferred.resolve();
            } else {
                deferred.reject();
            }
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    }

    Video.getCounts = function () {
        var deferred = $q.defer();
        $http.get('api/GetVideoCounts.php').success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    };

    Video.fetchMetadata = function (videoId, tmdbId) {
        var deferred = $q.defer();
        $http.get('api/FetchVideoMetadata.php', { params: { videoId: videoId, tmdbId: tmdbId } }).success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    };

    Video.getMetadataSearchResultsByTitle = function (mediaType, title) {
        var deferred = $q.defer();
        $http.get('api/GetMetadataSearchResults.php', {
            params: {
                mediaType: mediaType,
                title: title
            }
        }).success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    }

    Video.getMetadataSearchResultsBytmdbId = function (mediaType, tmdbId) {
        var deferred = $q.defer();
        $http.get('api/GetMetadataSearchResults.php', {
            params: {
                mediaType: mediaType,
                tmdbId: tmdbId
            }
        }).success(function (data) {
            deferred.resolve(data);
        }).error(function () {
            deferred.reject();
        });
        return deferred.promise;
    }

    Video.getShowFromEpisodeId = function (episodeId) {
        var deferred = $q.defer();
        $http.get('api/GetTvShowByEpisodeId.php?videoId=' + episodeId)
            .success(function (video) {
                deferred.resolve(video);
            })
            .error(function () {
                deferred.reject();
            });
        return deferred.promise;
    }

    /**
     * Finds a poster and metadata for every video in the library that does not have one yet.
     * @returns {$q@call;defer.promise}
     */
    Video.fetchMissingMetadata = function () {
        var deferred = $q.defer();
        $http.get('api/FetchMissingMetadataAndPosters.php')
            .success(function (result) {
                deferred.resolve(result);
            })
            .error(function () {
                deferred.reject();
            });
        return deferred.promise;
    }

    Video.addNewMediaItem = function (videoSourceId, newMediaItemPath) {
        return $http.get('api/AddNewMediaItem.php', { params: { videoSourceId: videoSourceId, path: newMediaItemPath } }).then(function (result) {
            return result.data;
        }, function (error) {
            return error;
        });
    };


    Video.getCategoryNames = function () {
        return $http.get('api/GetCategoryNames.php').then(function (result) {
            return result.data;
        }, function (error) {
            return error;
        });
    };

    Video.getCategories = function (names) {
        names = typeof names !== 'string' && typeof names.length === 'number' ? names : [];
        properties = ['videoId', 'posterModifiedDate', 'title', 'hdPosterUrl'];

        return $http.get('api/GetCategories.php', {
            params: {
                names: names.join(','),
                properties: properties ? properties.join(',') : undefined
            }
        }).then(function (result) {
            var videos = result.data.videos;
            var categories = result.data.categories;

            //categories only come with a list of video ids, so assemble an array of videos based on the video id list and the video map
            for (var i = 0; i < categories.length; i++) {
                var category = categories[i];
                category.videos = [];
                for (var j = 0; j < category.videoIds.length; j++) {
                    var videoId = category.videoIds[j];
                    var video = videos[videoId];
                    if (video) {
                        category.videos.push(video);
                    }
                }
                delete category.videoIds;
            }
            return categories;
        }, function (error) {
            return error;
        });
    };

    Video.processVideo = function (videoId) {
        return $http.get('api/ProcessVideo.php', { params: { videoId: videoId } }).then(function (response) {
            var result = response.data;
            if (result.success === true) {
                return result;
            } else {
                return $q.reject(result);
            }
        });
    };

    Video.addToList = function (listName, videoIds) {
        return $http.get('api/AddToList.php', { params: { videoIds: videoIds, listName: listName } });
    };

    Video.removeFromList = function (listName, videoIds) {
        return $http.get('api/RemoveFromList.php', { params: { videoIds: videoIds, listName: listName } });
    };

    Video.isInList = function (listName, videoId) {
        return $http.get('api/IsInList.php', { params: { videoId: videoId, listName: listName } }).then(function (response) {
            return response.data;
        });
    };

    Video.getListInfo = function (videoId) {
        return $http.get('api/GetVideoListInfo.php', { params: { videoId: videoId } }).then(function (response) {
            return response.data;
        });
    };
    return Video;
}]);
