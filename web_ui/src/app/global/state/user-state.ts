import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { API_response, TokenPayload, User } from '../types';
import { ProductState } from './product-state';
import { decode } from 'js-base64';
import { ApiService } from '../services/api.service';

@Injectable({
    providedIn: 'root'
})

export class UserState{

    current_user: User = undefined;

    current_user$: BehaviorSubject<User> = new BehaviorSubject<User>( this.current_user );

    constructor( private product_state: ProductState,
                 private api: ApiService ) {}

    autoLogin() {      //called in app.component.ts

        if ( localStorage.getItem( 'token' ) === null ) return;

        const token = localStorage.getItem( 'token' );

        //decode token
        this.decodeToken( token );
          
        //recover product state
        this.product_state.getProducts();
  
    }

    logout() {

        //setup body of request
        const body = { id: this.current_user.id };

        //call types.ts service and pass endpoint and object
        this.api.post( 'users/logout', body ).subscribe( 
            ( response: API_response ) => {
                
                //thow error if not successfull
                if ( response.success !== true )
                {
                    alert( response.message);
                    return;
                }

            }
        );

        this.clearUserState();

    }

    setUserState( user_incoming: User ) {

        this.current_user = user_incoming;

        this.current_user$.next( this.current_user );
    }

    clearUserState() {

        this.current_user = undefined;

        localStorage.removeItem( 'token' );

        this.current_user$.next( this.current_user );
    }

    decodeToken( token: string ) {

        var payload = token.split( '.' )[1]; 

        payload = decode( payload );

        var payload_JSON :TokenPayload = JSON.parse( payload );

        if ( this.expiredToken( payload_JSON.exp ) ) {
            this.clearUserState();
            return;
        }

        localStorage.setItem( 'token', token ); 

        this.setUserState( payload_JSON as User );
    }

    expiredToken( exp: number ) {
        
        const current_time = Date.now()/1000; //convert ms to s

        return ( exp < current_time );
    }
}
