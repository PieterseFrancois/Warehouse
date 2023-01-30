import { Injectable } from '@angular/core';
import {
    HttpRequest,
    HttpHandler,
    HttpEvent,
    HttpInterceptor,
    HttpResponse
} from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators'
import { UserState } from './state/user-state';

@Injectable({
    providedIn: 'root',
})

export class Interceptor implements HttpInterceptor {

    constructor( private user_state: UserState ) { }

    intercept( request: HttpRequest<unknown>, next: HttpHandler ): Observable<HttpEvent<any>> {

        const token = localStorage.getItem( 'token' );

        var request_final: HttpRequest<unknown> = request.clone();

        //test if token is set and set header accordingly
        if ( token !== null ) {
            request_final = request.clone({
                setHeaders: { Authorization: token }              
            });
        }

        //handle response
        return next.handle( request_final ).pipe(
            map( ( event ) => {
                        
                //no change to response
                if ( request.url.endsWith( 'users/register' ) ) return event;

                //boolean variables for tests
                if ( event instanceof HttpResponse && !!event.body ) {

                    if ( event.body.success === true && !!event.body.token ) {
                        //get token from body
                        const token = event.body.token;

                        this.user_state.decodeToken( token );

                        return event;
                    }

                    //test if token error and redirect to login
                    const message = event.body.message;

                    if (  message.indexOf( 'JWT_ERR' ) !== -1 ) {
                        this.user_state.clearUserState();   
                    }                     
                }
                    
                return event;
            })
        ); 
    }
}
