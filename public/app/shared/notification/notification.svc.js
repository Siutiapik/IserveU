(function() {

	'use strict';

	angular
		.module('iserveu')
		.service('notificationService', notificationService);


	function notificationService($stateParams, $state, $mdToast, auth, user, $rootScope, $mdDialog, ethnic_origin) {

		var vm = this;

		vm.fields = [];

		vm.id = JSON.parse(localStorage.getItem('user')).id;

		vm.reset = function(newpassword) {
			var data = {
				id: vm.id,
				password: newpassword
			}
			user.updateUser(data).then(function(result){
				$mdToast.show(
                  $mdToast.simple()
                    .content("Thank you for resetting your password!")
                    .position('bottom right')
                    .hideDelay(3000)
                );
			}, function(error){
				console.log(error);
			});
		}

		vm.getEmptyFields = getEmptyFields;

		function getUserFields(){
			user.editUser(vm.id).then(function(results){
				getEmptyFields(results);
			}, function(error) {
				console.log(error);
			});
		}

		function getEmptyFields(fields) {
			angular.forEach(fields, function(value,key) {
	    		delete fields[key].rules;
	    		value['key'] = value.name;
	    		delete fields[key].name;
				if(value.templateOptions.valueProp == null && value.key != 'password'){
					if(value.key == 'ethnic_origin_id'){
						ethnic_origin.getEthnicOrigins().then(function(results){
	    					var ethnics = results;
	    					value.templateOptions['ngRepeat'] = ethnics;
						});
					}
					vm.fields.push(value);
				}
				if(value.key == "date_of_birth" && value.templateOptions.valueProp == '0000-00-00'){
					vm.fields.push(value);
					showDialogBox();
					//$rootScope.$broadcast('dialogBox');	// using rootscope b/c $scope is not injectable
				}
			})
			return vm.fields;
		}

		getUserFields();

		// $rootScope.$on('dialogBox', function(events, data){
		// 	showDialogBox();
		// });

		function showDialogBox(){
			$mdDialog.show({
				controller: EditUserController,
				templateUrl: 'app/shared/notification/missingfields.tpl.html',
				parent: angular.element(document.body),
				clickOutsideToClose: false
			});
		}

		function showVerificationBox(){
			$mdDialog.show({
				controller: VerifyUserController,
				templateUrl: 'app/shared/notification/verificationform.tpl.html',
				parent: angular.element(document.body),
				clickOutsideToClose: false
			});
		}

		function EditUserController($scope, $mdDialog) {
	    	$scope.submit = function(model) {
	    		model['id'] = vm.id;
	    		user.updateUser(model).then(function(results){
	    			$mdDialog.hide();
	    		}, function(error){
	    			$mdDialog.cancel();
	    			console.log(error);
	    		})
	    	}
	    	$scope.cancel = function(){
	    		$mdDialog.cancel();
	    	}
		}

		function VerifyUserController($scope, $mdDialog) {
	    	$scope.verify = function(address) {
	    		address['user_id'] = vm.id;
	    		// api post to upload data
	    		$mdDialog.hide();
	    	}
	    	$scope.cancel = function(){
	    		$mdDialog.cancel();
	    	}
		}

	}


}());