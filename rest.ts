import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {GlobalSettingsProvider} from "../global-settings/global-settings";
import {AuthServiceProvider} from "../auth-service/auth-service";

/*
  Generated class for the RestProvider provider.

  See https://angular.io/guide/dependency-injection for more info on providers
  and Angular DI.
*/
@Injectable()
export class RestProvider {

    //apiUrl = 'https://jsonplaceholder.typicode.com';
    token: any;
    constructor(public http: HttpClient, public settings: GlobalSettingsProvider, public auth: AuthServiceProvider) {
        console.log('Hello RestProvider Provider');



    }

    /*createHeadersFromToken(token: string) {

        return new HttpHeaders({
            Authorization: token
        });
    }

    getToken() {
        this.storage.get('access_token').then(res => {
            if (res!=null && res!=undefined) {

                this.createHeadersFromToken('Bearer '+res);

                //this.token='Bearer '+res['access_token'];
            }
            else {
                //this.token=false;
            }

        });
    }*/



    getArticles(page?: number) {
        if (!page) {
            page=1;
        }
        return new Promise(resolve => {
            this.http.get(this.settings.myAPIUrl + '/articles?page=' + page).subscribe(data => {

                resolve(data);
            }, err => {
                console.log(err);
                resolve(err);
            });
        });
    }

    getArticle(id) {
        return new Promise(resolve => {
            this.http.get(this.settings.myAPIUrl + '/articles/' + id).subscribe(data => {
                resolve(data);
            }, err => {
                console.log(err);
                resolve(err);
            });
        });
    }


    getReciepes(page?: number, category_id?: number) {
        let query : string ='';

        if (category_id) {
            query = '&category_id=' + category_id;
        }
        else {
            query ='';
        }
        if (!page) {
            page=1;
        }
        return new Promise((resolve, reject)=> {
            this.http.get(this.settings.myAPIUrl + '/reciepes?page=' + page +query).subscribe(data => {
                console.log(data);
                resolve(data);
            }, err => {
                console.log(err);
                reject(err);
            });
        });
    }

    getReciepe(id) {
        return new Promise(resolve => {
            this.http.get(this.settings.myAPIUrl + '/reciepes/' + id).subscribe(data => {
                resolve(data);
            }, err => {
                console.log(err);
                resolve(err);
            });
        });
    }

    getUserMenu(page?: number) {


        return new Promise((resolve, reject)=> {

         //   this.storage.get('access_token').then(res => {

            //    if (res) {


                    this.http.get(this.settings.myAPIUrl + '/menus?page=' + page).subscribe(data => {
                        console.log(data);
                        resolve(data);
                    }, err => {
                        console.log(err);
                        reject(err);
                    });
             //   }
             //   else {



             //   }

           // });

        });
    }

    /*uploadImage(image) {

        return new Promise((resolve, reject)=> {
         //   let postData = {file: image};
           // postData.append('file', image);

                    let postData: any = {file: image};
                    //  postData.file=image;

                  //  console.log(image);
                    //  console.log(postData);

                    this.http.post(this.settings.myAPIUrl + '/menu/saveuserimage?access-token=Z4N43bmCGnPpstZFBVSZJ-qqAoel6Px-ZdI5m5X8', postData).subscribe(data => {
                        // console.log(data);
                        //  console.log('params '+data['file']);
                        resolve(data);
                    }, err => {
                        console.log(err);
                        reject(err);
                    });

        });
    }*/

    createMenu(menu) {


        return new Promise((resolve, reject) => {

                    let query: any;
                    if (menu.id) {
                        query = this.http.put(this.settings.myAPIUrl + '/menus/'+menu.id, menu);
                    }
                    else {
                        query = this.http.post(this.settings.myAPIUrl + '/menus', menu);
                    }

                    query.subscribe(data => {
                        console.log(data);
                        resolve(data);
                    }, err => {
                        console.log(err);
                        reject(err);
                    });


        });
    }
    saveText(text) {


        return new Promise((resolve, reject) => {

            /* let query: any;
          *  if (text.id) {
                 query = this.http.put(this.settings.myAPIUrl + '/tasks/'+text.id, text);
             }
             else {
                 query = this.http.post(this.settings.myAPIUrl + '/tasks', text);
             }*/

            this.http.post(this.settings.myAPIUrl + '/tasks', text).subscribe(data => {
                console.log(data);
                resolve(data);
            }, err => {
                console.log(err);
                reject(err);
            });


        });
    }
    getTasks() {

        return new Promise(resolve => {
            this.http.get(this.settings.myAPIUrl + '/tasks').subscribe(data => {

                resolve(data);
            }, err => {
                console.log(err);
                resolve(err);
            });
        });
    }
    getTask(id) {
        return new Promise(resolve => {
            this.http.get(this.settings.myAPIUrl + '/tasks/' + id).subscribe(data => {
                resolve(data);
            }, err => {
                console.log(err);
                resolve(err);
            });
        });
    }


}
