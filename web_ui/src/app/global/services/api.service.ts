import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http'

const API_URL = 'http://api.warehouse.com/';

@Injectable({
   providedIn: 'root'
})

export class ApiService {

    constructor( private http: HttpClient ) { }

    post( endpoint: string, dataObject? : Object ) {

        const response$ = this.http.post(        
            API_URL + endpoint,     
            dataObject,
        );     
        
        return response$;
    }
}