var mainApp = angular.module('mainApp',['infinite-scroll']).config(function($interpolateProvider){
    $interpolateProvider.startSymbol('[[').endSymbol(']]');
});


mainApp.service('endpoints', function($http,$q){
    return {
        getMovies: function(limit,offset,sort,direction,author) {
            var url = '/movies/'+limit+'/'+offset+'/'+sort+'/'+direction+'/'+author;
            console.log(url);
            var promise = $http.get(url).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        },
        likeMovie: function(id) {
            var url = '/user/like/'+id;
            console.log(url);
            var promise = $http.post(url).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        },
        deleteMovie: function(id) {
            var url = '/user/delete/'+id;
            console.log(url);
            var promise = $http.post(url).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        },
        editMovie: function(data) {
            var url = '/user/edit/movie';
            console.log(url);
            var promise = $http.post(url,data).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        },
        unlikeMovie: function(id) {
            var url = '/user/unlike/'+id;
            console.log(url);
            var promise = $http.post(url).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        },
        hateMovie: function(id) {
            var url = '/user/hate/'+id;
            console.log(url);
            var promise = $http.post(url).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        },
        unhateMovie: function(id) {
            var url = '/user/unhate/'+id;
            console.log(url);
            var promise = $http.post(url).then(function(success){
                return success.data;
            }, function(error){
                console.log(error);
            });
            return promise;
        }
    };
});


mainApp.controller('MainController',
    function($scope, endpoints,$timeout){
            $scope.limit = 10;
            $scope.offset = 0;
            $scope.movies = [];
            $scope.loading = 0;
            $scope.has_more = 1;
            $scope.sort = 'created';
            $scope.direction = 'desc';
            $scope.author = null;
            $scope.author_name = null;
            $scope.delete_movie = {
                name: null,
                id: null,
                index: null,
                flash:null
            };

            $scope.edit_movie = {
                title: null,
                id: null,
                flash:null,
                index:null,
                description: null
            };

            $scope.fetch = function(){
                if($scope.loading == 0){
                    $scope.loading = 1;
                    endpoints.getMovies($scope.limit,$scope.offset,$scope.sort,$scope.direction,$scope.author).then(function(data){
                        console.log(data);
                        if(data.movies.length > 0){
                            for(var i=0;i<data.movies.length;i++){
                                var movie = data.movies[i];
                                $scope.movies.push(movie);
                                $scope.offset++;
                            }
                        }
                        $scope.loading = 0;

                    });
                }
            };

            $scope.ago = function(time) {
                var seconds = new Date().getTime() / 1000 - time;
                if (30 > seconds) return "now";
                if (60 > seconds) return Math.floor(seconds) + "s ago";
                var minutes = Math.floor(seconds / 60);
                if (60 > minutes) return minutes + "m ago";
                var hours = Math.floor(minutes / 60);
                if (24 > hours) return hours + "h ago";
                var days = Math.floor(hours / 24);
                return days + "d ago"
            };

            $scope.likeMovie = function(index){
               var movie = $scope.movies[index];
               endpoints.likeMovie(movie.id).then(function(data){
                   console.log(data);
                   if(data.saved == 1) {
                       $scope.movies[index].likes = $scope.movies[index].likes + 1;
                       $scope.movies[index].hasHated = 0;
                       $scope.movies[index].hasLiked = 1;
                       if(movie.hates>0){
                           $scope.movies[index].hates = $scope.movies[index].hates - 1;
                       }
                   }
               });
            };

            $scope.hateMovie = function(index){
                var movie = $scope.movies[index];
                endpoints.hateMovie(movie.id).then(function(data){
                    console.log(data);
                    if(data.saved == 1) {
                        $scope.movies[index].hates = $scope.movies[index].hates + 1;
                        $scope.movies[index].hasHated = 1;
                        $scope.movies[index].hasLiked = 0;

                        if(movie.likes>0){
                            $scope.movies[index].likes = $scope.movies[index].likes - 1;
                        }
                    }
                });
            };

            $scope.unlikeMovie = function(index){
                var movie = $scope.movies[index];
                endpoints.unlikeMovie(movie.id).then(function(data){
                    console.log(data);
                    if(data.saved == 1) {
                        $scope.movies[index].likes = $scope.movies[index].likes - 1;
                        $scope.movies[index].hasLiked = 0;

                    }
                });
            };

            $scope.unhateMovie = function(index){
                var movie = $scope.movies[index];
                endpoints.unhateMovie(movie.id).then(function(data){
                    console.log(data);
                    if(data.saved == 1) {
                        $scope.movies[index].hates = $scope.movies[index].hates - 1;
                        $scope.movies[index].hasHated = 0;

                    }
                });
            };

            $scope.setSort = function(sort){
              $scope.sort = sort;
              $scope.movies = [];
              $scope.offset = 0;
              $scope.fetch();
            };
            $scope.setDirection = function(direction){
                $scope.direction = direction;
                $scope.movies = [];
                $scope.offset = 0;
                $scope.fetch();
            };
            $scope.setAuthor = function(id,username){
                $scope.author = id;
                $scope.author_name = username;
                $scope.movies = [];
                $scope.offset = 0;
                $scope.fetch();
            };

            $scope.unsetAuthor = function(){
                $scope.author = null;
                $scope.author_name = null;
                $scope.movies = [];
                $scope.offset = 0;
                $scope.fetch();
            };

            $scope.deleteClick = function(index){
                var movie = $scope.movies[index];
                $scope.delete_movie.id = movie.id;
                $scope.delete_movie.name = movie.title;
                $scope.delete_movie.index = index;
            };

            $scope.performDelete = function () {
                endpoints.deleteMovie($scope.delete_movie.id).then(function(data){
                    console.log(data);
                    if(data.saved == 1) {
                        delete $scope.movies[$scope.delete_movie.index];
                        $scope.delete_movie.id = null;
                        $scope.delete_movie.name = null;
                        $scope.delete_movie.index = null;
                        $scope.delete_movie.flash = 1;

                    }else{
                        $scope.delete_movie.flash = -1;
                    }
                    $timeout(function(){
                        $scope.delete_movie.flash = null;
                    },5000);
                });
            };

            $scope.editClick = function(index){
                var movie = $scope.movies[index];
                $scope.edit_movie.id = movie.id;
                $scope.edit_movie.index = index;
                $scope.edit_movie.title = movie.title;
                $scope.edit_movie.description = movie.description;
            };

            $scope.saveMovie = function(){
                var post_data = {
                   id: $scope.edit_movie.id,
                   title: $scope.edit_movie.title,
                   description: $scope.edit_movie.description
                };
                endpoints.editMovie(post_data).then(function(data){
                    console.log(data);
                    if(data.saved == 1) {
                         $scope.movies[$scope.edit_movie.index].title = $scope.edit_movie.title;
                         $scope.movies[$scope.edit_movie.index].description = $scope.edit_movie.description;
                        $scope.edit_movie.id = null;
                        $scope.edit_movie.title = null;
                        $scope.edit_movie.description = null;
                        $scope.edit_movie.index = null;
                        $scope.edit_movie.flash = 1;

                    }else{
                        $scope.edit_movie.flash = -1;
                    }
                    $timeout(function(){
                        $scope.edit_movie.flash = null;
                    },5000);
                });
            }
    }
);