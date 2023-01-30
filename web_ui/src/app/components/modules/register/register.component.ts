import { Component } from '@angular/core';
import { User, API_response } from 'src/app/global/types';
import { ApiService } from 'src/app/global/services/api.service';
import { Router } from '@angular/router';
import { routes } from 'src/app/app-routing.module';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss',]
})
export class RegisterComponent {

    match: boolean = false;

    constructor( private api : ApiService, private router: Router ) { }

    passwordCompare( password: string, password_confirm: string ) { 
        
        if ( password === password_confirm )
            this.match = true;
        else
            this.match = false;
    }

    registerUser( input: User ) {

        const user: User = {
            email : input.email,
            password : input.password,
            name : input.name,
            role : input.role,
        }

        //call types.ts service and pass endpoint and object
        this.api.post( 'users/create', user ).subscribe( 
            ( response: API_response ) => {
                
                //thow error if not successfull
                if ( response.success !== true )
                {
                    alert( response.message );
                    return;
                }
                
                //temp
                alert( 'Registration successful' );

                //redirect to login
                const login_route: string = routes.find( element => element.title === 'Login' ).path;

                this.router.navigateByUrl( login_route );
            }
        );

    }

}