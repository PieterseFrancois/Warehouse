import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { routes } from 'src/app/app-routing.module';
import { User, API_response } from 'src/app/global/types';
import { ApiService } from 'src/app/global/services/api.service';
import { UserState } from 'src/app/global/state/user-state';
import { ProductState } from 'src/app/global/state/product-state';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss',]
})

export class LoginComponent {

    //routerLink
    register_route: string = routes.find( element => element.title === 'Register' ).path;

    constructor( private router: Router, 
                 private api: ApiService,
                 private product_state: ProductState )
    { }

    loginUser( input: User ) {

        //call types.ts service and pass endpoint and object
        this.api.post( 'users/login', input ).subscribe( 
            ( response: API_response ) => {
                
                //thow error if not successfull
                if ( response.success !== true )
                {
                    alert( response.message);
                    return;
                }
                
                //temp
                alert( 'Login successful' );

                //start process to retrieve products
                this.product_state.getProducts();

                //redirect to profile page
                const profile_route = routes.find( element => element.title === 'Profile' ).path;
                this.router.navigateByUrl( profile_route );

            }
        );
    }
}
