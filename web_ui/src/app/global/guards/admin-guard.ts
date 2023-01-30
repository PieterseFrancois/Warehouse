import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { UserState } from '../state/user-state';
import { User } from '../types';
import { routes } from 'src/app/app-routing.module';

@Injectable({
    providedIn: 'root'
})

export class AdminGuard implements CanActivate {

    constructor( private user_state: UserState, private router: Router ) {}

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {

        var result: boolean = false;

        this.user_state.current_user$.subscribe(
            ( user: User ) => {

                result = user !== undefined && user.role === 'admin';
            }
        );

        if ( !result )
        {
            const login_route: string = routes.find( element => element.title === 'Login' ).path;
            this.router.navigateByUrl( login_route ); 
        }

        return result;
    }
}
